<?php

namespace App\Domain\Webhook;

class RetryPolicy
{
    private int $maxRetries = 5;

    public function shouldRetry(int $currentRetryCount): bool
    {
        return $currentRetryCount < $this->maxRetries;
    }

    public function getNextRetryDelay(int $currentRetryCount): int
    {
        return min(60, pow(2, $currentRetryCount));
    }
}
