<?php

namespace IanRothmann\LangServePhpClient;

use IanRothmann\LangServePhpClient\Responses\RemoteRunnableResponse;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RemoteRunnablePool
{
    protected array $pool = [];
    protected HttpClientInterface $client;

    protected $headers = [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json'
    ];

    public function __construct()
    {
        $this->client = HttpClient::create();
    }

    public function invoke($id, $url, $input, $config = []): self
    {
        $headers = $this->getHeaders();
        $url=rtrim($url, '/');
        $data = ['input' => $input, 'config' => $config];
        $this->pool[] =  $this->client->request(
            'POST',
            $url . '/invoke',
            [
                'headers' => $headers,
                'body' => json_encode($data),
                'user_data' => $id
            ]
        );
        return $this;
    }

    public function wait(): array
    {
        $responses = [];
        foreach ($this->client->stream($this->pool) as $response => $chunk) {
            if ($chunk->isLast()) {
                $id = $response->getInfo('user_data');
                $responses[$id] = new RemoteRunnableResponse($response->toArray());
            }
        }

        return $responses;
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

    protected function getHeaders()
    {
        return $this->headers;
    }
}