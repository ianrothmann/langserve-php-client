<?php
namespace IanRothmann\LangServePhpClient;

use IanRothmann\LangServePhpClient\Exceptions\InternalServerErrorException;
use IanRothmann\LangServePhpClient\Exceptions\MalformedInputException;
use IanRothmann\LangServePhpClient\Exceptions\NotFoundException;
use IanRothmann\LangServePhpClient\Exceptions\RemoteInvocationException;
use IanRothmann\LangServePhpClient\Responses\RemoteRunnableBatchResponse;
use IanRothmann\LangServePhpClient\Responses\RemoteRunnableResponse;
use IanRothmann\LangServePhpClient\Responses\RemoteRunnableStreamEvent;
use IanRothmann\LangServePhpClient\Responses\RemoteRunnableStreamResponse;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class RemoteRunnable
{
    protected string $baseUrl;
    protected EventSourceHttpClient $client;

    protected $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];

    public function __construct($baseUrl)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->client = new EventSourceHttpClient(HttpClient::create());
    }

    /**
     * @throws TransportExceptionInterface
     * @throws MalformedInputException
     * @throws DecodingExceptionInterface
     * @throws NotFoundException
     * @throws RemoteInvocationException
     * @throws InternalServerErrorException
     */
    public function invoke($input, $config = []): RemoteRunnableResponse
    {
        return new RemoteRunnableResponse($this->sendRequest('/invoke', ['input' => $input, 'config' => $config]));
    }

    /**
     * @throws TransportExceptionInterface
     * @throws MalformedInputException
     * @throws DecodingExceptionInterface
     * @throws NotFoundException
     * @throws InternalServerErrorException
     * @throws RemoteInvocationException
     */
    public function batch(array $inputs, $config = []): RemoteRunnableBatchResponse
    {
        return new RemoteRunnableBatchResponse($this->sendRequest('/batch', ['inputs' => $inputs, 'config' => $config]));
    }

    /**
     * @throws TransportExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RemoteInvocationException
     */
    public function stream($input, callable $callback = null, $config = []): RemoteRunnableStreamResponse
    {
        try {
            $response = $this->sendRequest('/stream', ['input' => $input, 'config' => $config], false);
            $completeResult = new RemoteRunnableStreamResponse();
            foreach ($this->client->stream($response) as $chunk) {
                if ($chunk instanceof ServerSentEvent) {
                    $data = $chunk->getData();
                    if($data){
                        $event = RemoteRunnableStreamEvent::fromJson($data);
                        $completeResult->addEvent($event);
                        if (is_callable($callback)) {
                            try{
                                call_user_func($callback, $event);
                            }catch (\Throwable $e){
                                throw $e;
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Handle or log stream-specific exceptions here
            throw new RemoteInvocationException("Streaming failed: " . $e->getMessage(), $e->getCode(), $e);
        }

        return $completeResult;
    }

    /**
     * @throws TransportExceptionInterface
     * @throws MalformedInputException
     * @throws DecodingExceptionInterface
     * @throws NotFoundException
     * @throws RemoteInvocationException
     * @throws InternalServerErrorException
     */
    private function sendRequest($path, $data, $expectJson = true)
    {
        $headers = $this->getHeaders();
        try {
            $response = $this->client->request(
                'POST',
                $this->baseUrl . $path,
                [
                    'headers' => $headers,
                    'body' => json_encode($data),
                    'buffer' => $expectJson
                ]
            );
            return $expectJson ? $response->toArray() : $response;
        } catch (HttpExceptionInterface $e) {
            $this->handleHttpException($e);
        } catch (TransportExceptionInterface $e) {
            throw new RemoteInvocationException("Network error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @throws TransportExceptionInterface
     * @throws MalformedInputException
     * @throws NotFoundException
     * @throws InternalServerErrorException
     * @throws RemoteInvocationException
     */
    protected function handleHttpException(HttpExceptionInterface $e)
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $message = $e->getMessage();
        switch ($statusCode) {
            case 422:
                throw new MalformedInputException($message, $statusCode, $e);
            case 404:
                throw new NotFoundException($message, $statusCode, $e);
            case 500:
                throw new InternalServerErrorException($message, $statusCode, $e);
            default:
                throw new RemoteInvocationException($message, $statusCode, $e);
        }
    }

    public function addHeader($key, $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }

    public function authenticateWithBearerToken($token): self
    {
        $this->headers['Authorization'] = 'Bearer ' . $token;
        return $this;
    }

    public function authenticateWithXToken($token): self
    {
        $this->headers['X-Token'] = $token;
        return $this;
    }

    public function withTraceId($traceId): self
    {
        if($traceId){
            $this->headers['X-Trace-Id'] = $traceId;
        }
        return $this;
    }

    protected function getHeaders()
    {
        return $this->headers;
    }
}
