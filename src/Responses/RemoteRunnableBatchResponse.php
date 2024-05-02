<?php

namespace IanRothmann\LangServePhpClient\Responses;

class RemoteRunnableBatchResponse
{
    protected array $responses = [];


    public function __construct(?array $responses)
    {
        foreach ($responses as $response) {
            if($response){
                $this->responses[] = new RemoteRunnableResponse($response);
            }
        }
    }

    public function addResponse(RemoteRunnableResponse $response): self
    {
        $this->responses[] = $response;
        return $this;
    }

    public function getResponses(): array
    {
        return $this->responses;
    }

    public function toJson(): string
    {
        return json_encode([
            'responses' => array_map(function($response){
                return json_decode($response->toJson(), true);
            }, $this->responses)
        ]);
    }
}
