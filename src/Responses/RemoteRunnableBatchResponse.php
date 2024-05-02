<?php

namespace IanRothmann\LangServePhpClient\Responses;

class RemoteRunnableBatchResponse
{
    protected array $responses = [];


    public function __construct(?array $responses)
    {
        foreach ($responses['output'] ?? [] as $key => $response) {
            if($response){
                $template = [];
                $template['output'] = $response;
                $template['metadata']['run_id'] = $response['metadata']['run_ids'][$key] ?? null;
                $this->responses[] = new RemoteRunnableResponse($template);
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
