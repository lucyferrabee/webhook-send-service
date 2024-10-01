<?php

namespace App\Domain\Webhook;

class Webhook
{
    private string $id;
    private string $url;
    private string $name;
    private string $event;
    private int $retryCount = 0;

    public function __construct(string $id, string $url, string $name, $event)
    {
        $this->id = $id;
        $this->url = $url;
        $this->name = $name;
        $this->event = $event;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getPayload(): array
    {
        return [
            $this->name,
            $this->event,
            $this->id
        ];
    }

    public function getRetryCount(): string
    {
        return $this->retryCount;
    }
}
