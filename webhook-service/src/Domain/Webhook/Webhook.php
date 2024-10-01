<?php

namespace App\Domain\Webhook;

class Webhook
{
    private string $id;
    private string $url;
    private string $payload;
    private int $retryCount = 0;
    private int $maxRetries = 5;

    public function __construct(string $id, string $url, string $payload)
    {
        $this->id = $id;
        $this->url = $url;
        $this->payload = $payload;
    }

    public function getRetryDelay(): int
    {
        return min(60, pow(2, $this->retryCount));
    }

    public function incrementRetryCount(): void
    {
        $this->retryCount++;
    }

    public function hasExceededMaxRetries(): bool
    {
        return $this->retryCount >= $this->maxRetries;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getRetryCount(): string
    {
        return $this->retryCount;
    }
}
