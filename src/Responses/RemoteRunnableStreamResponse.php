<?php

namespace IanRothmann\LangServePhpClient\Responses;

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

        if($event->getContentAsString()){
            $this->content .= $event->getContentAsString();
        }

        $this->events[] = $event;
        return $this;
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function getContentAsString(): ?string
    {
        return $this->content;
    }

    public function getContent(): mixed
    {
        $string=$this->getContentAsString();
        $result=json_decode($string, true);
        if(json_last_error()==JSON_ERROR_NONE){
            return $result;
        }else{
            return $string;
        }
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

    public static function mock($output): self
    {
        if(is_array($output)){
            $output = json_encode($output);
        }
        $response = new self();
        $response->addEvent(new RemoteRunnableStreamEvent([
            'content' => $output,
        ]));

        return $response;
    }

}
