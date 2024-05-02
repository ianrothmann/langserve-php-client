<?php

namespace IanRothmann\LangSmithPhpClient\Responses;

class RemoteRunnableStreamResponse
{
    protected array $events = [];
    protected $runId = null;
    protected $content = '';

    public function addEvent(RemoteRunnableStreamEvent $event): self
    {
        if($event->getRunId()){
            $this->runId = $event->getRunId();
        }

        if($event->getContent()){
            $this->content .= $event->getContent();
        }

        $this->events[] = $event;
        return $this;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function toJson(): string
    {
        return json_encode([
            'run_id' => $this->runId,
            'content' => $this->content,
            'events' => array_map(function($event){
                return $event->getData();
            }, $this->events)
        ]);
    }

}
