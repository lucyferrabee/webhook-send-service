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

        // Check if this endpoint has already failed 5 times and should be skipped
        if (isset($this->failedEndpoints[$endpoint]) && $this->failedEndpoints[$endpoint] >= 5) {
            echo "Skipping webhook for {$endpoint} due to too many previous failures.\n";
            return;
        }

        $delay = 1; // Start with an initial delay of 1 second

        // Retry until the delay reaches 60 seconds
        while ($delay <= 60) {
            try {
                // Attempt to send the webhook
                if ($this->sender->send($webhook)) {
                    echo "Webhook sent successfully to {$endpoint}\n";
                    return; // Successfully sent, stop retrying
                } else {
                    throw new Exception("Failed to send webhook to {$endpoint}");
                }
            } catch (Exception $e) {
                // If the delay is already 60 seconds, mark it as a failure
                if ($delay >= 60) {
                    echo "Webhook failed after reaching 60 seconds delay for {$endpoint}.\n";
                    $this->registerFailedEndpoint($endpoint);
                    break;
                }

                // Retry with exponential backoff
                echo "Retrying {$endpoint} in {$delay} seconds\n";
                $this->sleep($delay);
                $delay = min($delay * 2, 60);  // Double the delay, but cap at 60 seconds
            }
        }
    }

    // Register a failure for a particular endpoint
    private function registerFailedEndpoint(string $endpoint): void
    {
        if (!isset($this->failedEndpoints[$endpoint])) {
            $this->failedEndpoints[$endpoint] = 0;
        }

        $this->failedEndpoints[$endpoint]++;

        // Check if the failure count has reached 5
        if ($this->failedEndpoints[$endpoint] >= 5) {
            echo "Endpoint {$endpoint} has failed 5 times. No further attempts will be made for this endpoint.\n";
        }
    }

    // Simulate sleep to handle delays between retries
    protected function sleep(int $seconds): void
    {
        sleep($seconds);
    }
}
