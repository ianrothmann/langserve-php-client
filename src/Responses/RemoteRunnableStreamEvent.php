<?php

namespace IanRothmann\LangServePhpClient\Responses;

class RemoteRunnableStreamEvent
{

    protected array $data = [];

    public function __construct(?array $data)
    {
        $this->data = $data ?? [];
    }

    public function getContentAsString(): ?string
    {
        if(array_key_exists('content', $this->data) && array_key_exists('type', $this->data) && $this->data['type']=='AIMessageChunk'){
            if(is_array($this->data['content'])) {
                return json_encode($this->data['content']);
            } else {
                return $this->data['content'] ?? null;
            }
        }elseif(sizeof($this->data)==1 && array_key_exists('run_id', $this->data)){
            return null;
        }else{
            return json_encode($this->data);
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

    public function getType(): ?string
    {
        return $this->data['type'] ?? null;
    }

    public function getRunId(): ?string
    {
        return $this->data['run_id'] ?? null;
    }

    public function hasContent(): bool
    {
        return !!$this->getContent();
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

    public static function fromArray($array): ?self
    {
        return new self($array);
    }
}
