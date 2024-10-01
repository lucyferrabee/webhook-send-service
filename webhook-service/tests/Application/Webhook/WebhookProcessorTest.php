<?php

namespace App\Tests\Application\Webhook;

use PHPUnit\Framework\TestCase;
use App\Application\Webhook\WebhookProcessor;
use App\Domain\Webhook\Webhook;
use App\Infrastructure\Http\WebhookSender;

class WebhookProcessorTest extends TestCase
{
    private WebhookSender $webhookSender;
    private WebhookProcessor $processor;

    protected function setUp(): void
    {
        $this->webhookSender = $this->createMock(WebhookSender::class);
        $this->processor = $this->getMockBuilder(WebhookProcessor::class)
            ->setConstructorArgs([$this->webhookSender])
            ->onlyMethods(['sleep'])
            ->getMock();
    }

    public function testSuccessfulWebhookSend()
    {
        $this->webhookSender->method('send')->willReturn(true);

        $webhook = new Webhook(1, "https://webhook-test.info1100.workers.dev/success1", "Olimpia Krasteva", "Spooky Summit");
        $this->processor->process($webhook);

        $this->assertEquals(0, $webhook->getRetryCount());
    }

    public function testWebhookExponentialBackoff()
    {
        $this->webhookSender->expects($this->exactly(4))
            ->method('send')
            ->willReturnOnConsecutiveCalls(false, false, false, true);

        $webhook = new Webhook(6, "https://webhook-test.info1100.workers.dev/retry1", "Neha Lebeau", "Fall Foliage Farm");

        $this->processor->expects($this->exactly(3))
        ->method('sleep')
            ->withConsecutive([1], [2], [4]);

        $this->processor->process($webhook);

        $this->assertEquals(3, $webhook->getRetryCount());
    }

    public function testMaxRetryDurationHandling()
    {
        $this->webhookSender->method('send')->willReturn(false);

        $webhook = new Webhook(2, "https://webhook-test.info1100.workers.dev/fail1", "Kumaran Powell", "Serene Sands");

        $this->processor->expects($this->exactly(6))
            ->method('sleep')
            ->withConsecutive([1], [2], [4], [8], [16], [32]);

        $this->processor->process($webhook);

        $this->assertEquals(6, $webhook->getRetryCount());
    }

    public function testSkipWebhooksAfterMaxFailures()
    {
        $this->webhookSender->method('send')->willReturn(false);

        $webhooks = [
            new Webhook(2, "https://webhook-test.info1100.workers.dev/fail1", "Kumaran Powell", "Serene Sands"),
            new Webhook(5, "https://webhook-test.info1100.workers.dev/fail1", "Suada Katz", "Serene Sands"),
            new Webhook(6, "https://webhook-test.info1100.workers.dev/fail1", "Another Person", "Serene Sands"),
            new Webhook(3, "https://webhook-test.info1100.workers.dev/fail1", "Yet Another", "Serene Sands"),
            new Webhook(1, "https://webhook-test.info1100.workers.dev/fail1", "Another Chance", "Serene Sands"),
            new Webhook(4, "https://webhook-test.info1100.workers.dev/fail1", "Last Chance", "Serene Sands"),
        ];

        $this->processor->expects($this->any())
            ->method('sleep');

        foreach ($webhooks as $webhook) {
            $this->processor->process($webhook);
        }

        $this->assertEquals(6, $webhooks[0]->getRetryCount());
        $this->assertEquals(6, $webhooks[1]->getRetryCount());
        $this->assertEquals(6, $webhooks[2]->getRetryCount());
        $this->assertEquals(6, $webhooks[3]->getRetryCount());
        $this->assertEquals(6, $webhooks[4]->getRetryCount());
        $this->assertEquals(0, $webhooks[5]->getRetryCount());
    }
}
