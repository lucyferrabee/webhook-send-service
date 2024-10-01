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
            echo "Skipping webhook for {$endpoint} due to too many failures.\n";
            return;
        }

        $retryCount = 0;
        $maxRetries = 5;
        $delay = 1;

        while ($retryCount < $maxRetries) {
            try {
                if ($this->sender->send($webhook)) {
                    echo "Webhook sent successfully to {$endpoint}\n";
                    return;
                } else {
                    throw new Exception("Failed to send webhook to {$endpoint}");
                }
            } catch (Exception $e) {
                $retryCount++;
                $webhook->incrementRetryCount();

                // Check if max retries have been reached
                if ($retryCount >= $maxRetries) {
                    echo "Max retries reached for {$endpoint}. Webhook failed.\n";
                    $this->registerFailedEndpoint($endpoint);
                    break;
                }

                echo "Retrying {$endpoint} in {$delay} seconds (attempt {$retryCount})\n";
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
    }

    protected function sleep(int $seconds): void
    {
        sleep($seconds);
    }
}
