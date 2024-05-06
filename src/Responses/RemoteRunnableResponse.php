<?php

namespace IanRothmann\LangServePhpClient\Responses;

class RemoteRunnableResponse
{

    protected array $data = [];

    public function __construct(?array $data)
    {
        $this->data = $data ?? [];
    }

    public function getContent(): ?string
    {
        if(is_array($this->data['output'])){
            if(sizeof($this->data['output'])==1 && array_key_exists('content', $this->data['output'])){
                return $this->data['output']['content'] ?? null;
            }
        }elseif (is_array($this->data['output'])){
            return json_encode($this->data['output']);
        }

        return $this->data['output'] ?? null;
    }

    public function getJsonContent(): ?array
    {
        return json_decode($this->getContent(), true);
    }

    public function getRunId(): ?string
    {
        return $this->data['metadata']['run_id'] ?? null;
    }

    public function getTokenUsage(): ?array
    {
        return $this->data['output']['response_metadata']['token_usage'] ?? null;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function toJson(): string
    {
        return json_encode($this->data);
    }

    public static function fromJson($json): ?self
    {
        return new self(json_decode($json, true));
    }

    public static function mock($output): self
    {
        if(is_array($output)){
            $output = json_encode($output);
        }

        return new self([
            'output' => [
                'content' => $output,
                'response_metadata' => [
                    'token_usage' => []
                ]
            ],
            'metadata' => [
                'run_id' => uniqid()
            ]
        ]);
    }
}
