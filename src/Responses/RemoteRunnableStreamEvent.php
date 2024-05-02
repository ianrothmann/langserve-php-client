<?php

namespace IanRothmann\LangServePhpClient\Responses;

class RemoteRunnableStreamEvent
{

    protected array $data = [];

    public function __construct(?array $data)
    {
        $this->data = $data ?? [];
    }

    public function getContent(): ?string
    {
        return $this->data['content'] ?? null;
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
        return isset($this->data['content']);
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
}
