<?php

namespace App\Application\Webhook;

use App\Domain\Webhook\Webhook;
use App\Infrastructure\Http\WebhookSender;
use Exception;

class WebhookProcessor
{
    private WebhookSender $sender;
    private array $failedEndpoints = [];

    public function __construct(WebhookSender $sender)
    {
        $this->sender = $sender;
    }

    public function process(Webhook $webhook): void
    {
        $endpoint = $webhook->getUrl();

        if (isset($this->failedEndpoints[$endpoint]) && $this->failedEndpoints[$endpoint] >= 5) {
            echo "Skipping webhook for {$endpoint} due to too many previous failures.\n";
            return;
        }

        $delay = 1;

        while ($delay <= 60) {
            try {
                if ($this->sender->send($webhook)) {
                    echo "Webhook sent successfully to {$endpoint}\n";
                    return;
                } else {
                    throw new Exception("Failed to send webhook to {$endpoint}");
                }
            } catch (Exception $e) {
                if ($delay >= 60) {
                    echo "Webhook failed after reaching 60 seconds delay for {$endpoint}.\n";
                    $this->registerFailedEndpoint($endpoint);
                    break;
                }

                echo "Retrying {$endpoint} in {$delay} seconds\n";
                $webhook->increaseRetryCount();
                $this->sleep($delay);
                $delay = min($delay * 2, 60);
            }
        }
    }

    private function registerFailedEndpoint(string $endpoint): void
    {
        if (!isset($this->failedEndpoints[$endpoint])) {
            $this->failedEndpoints[$endpoint] = 0;
        }

        $this->failedEndpoints[$endpoint]++;

        if ($this->failedEndpoints[$endpoint] >= 5) {
            echo "Endpoint {$endpoint} has failed 5 times. No further attempts will be made for this endpoint.\n";
        }
    }

    protected function sleep(int $seconds): void
    {
        sleep($seconds);
    }
}
