<?php

namespace IanRothmann\LangServePhpClient\Responses;

class RemoteRunnableResponse
{

    protected array $data = [];

    public function __construct(?array $data)
    {
        $this->data = $data ?? [];
    }

    public function getContentAsString(): ?string
    {
        if(is_array($this->data['output']) && array_key_exists('content', $this->data['output']) && array_key_exists('response_metadata', $this->data['output'])) {
            if(is_array($this->data['output']['content'])) {
                return json_encode($this->data['output']['content']);
            } else {
                return $this->data['output']['content'] ?? null;
            }
        }elseif (is_array($this->data['output'])){
            return json_encode($this->data['output']);
        }else{
            return $this->data['output'] ?? null;
        }
    }

    public function getContent()
    {
        $string=$this->getContentAsString();
        $result=json_decode($string, true);
        if(json_last_error()==JSON_ERROR_NONE){
            return $result;
        }else{
            return $string;
        }
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
            'output' => $output,
            'metadata' => [
                'run_id' => uniqid()
            ]
        ]);
    }
}
