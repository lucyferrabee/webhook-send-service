<?php

namespace App\Application\Webhook;

use App\Domain\Webhook\Webhook;
use App\Infrastructure\Http\WebhookSender;

class WebhookProcessor
{
    private WebhookSender $sender;

    public function __construct(WebhookSender $sender)
    {
        $this->sender = $sender;
    }

    public function process(Webhook $webhook): void
    {
        $retryCount = 0;

        while (!$webhook->hasExceededMaxRetries()) {
            $success = $this->sender->send($webhook);

            if ($success) {
                break;
            }

            $retryCount++;
            $webhook->incrementRetryCount();
            sleep($webhook->getRetryDelay());
        }

        var_dump('exceeded max retries, continuing to next webhook');
    }
}
