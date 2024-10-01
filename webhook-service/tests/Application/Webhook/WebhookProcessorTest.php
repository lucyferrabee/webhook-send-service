<?php

namespace App\Tests\Application\Webhook;

use PHPUnit\Framework\TestCase;
use App\Application\Webhook\WebhookProcessor;
use App\Domain\Webhook\Webhook;
use App\Infrastructure\Http\WebhookSender;
use App\Application\Webhook\WebhookLoader;

class WebhookProcessorTest extends TestCase
{
    /**
     * @var mixed|\PHPUnit\Framework\MockObject\MockObject
     */
    private $webhookSender;
    /**
     * @var WebhookProcessor
     */
    private $processor;
    /**
     * @var WebhookLoader
     */
    private $loader;

    protected function setUp(): void
    {
        $this->webhookSender = $this->createMock(WebhookSender::class);
        $this->processor = new WebhookProcessor($this->webhookSender);
    }

    public function testLoadWebhooksFromFile()
    {
        $webhooks = $this->loader->loadWebhooksFromFile(__DIR__ . '/../../Fixtures/webhooks.txt');

        $this->assertCount(15, $webhooks);

        $this->assertEquals('https://webhook-test.info1100.workers.dev/success1', $webhooks[0]->getUrl());
        $this->assertEquals(1, $webhooks[0]->getPayload()['order_id']);
    }

    public function testSuccessfulWebhookSend()
    {
        $this->webhookSender->method('send')->willReturn(true);

        $webhook = new Webhook("https://webhook-test.info1100.workers.dev/success1", 1, "Olimpia Krasteva", "Spooky Summit");
        $this->processor->process($webhook);

        $this->assertEquals(0, $webhook->getRetryCount());
    }

    public function testWebhookExponentialBackoff()
    {
        $this->webhookSender->expects($this->exactly(4))
            ->method('send')
            ->willReturnOnConsecutiveCalls(false, false, false, true);

        $webhook = new Webhook("https://webhook-test.info1100.workers.dev/retry1", 6, "Neha Lebeau", "Fall Foliage Farm");

        $this->mockSleep();
        $this->processor->process($webhook);

        $this->assertEquals(3, $webhook->getRetryCount());
    }

    public function testMaxRetryCountHandling()
    {
        $this->webhookSender->method('send')->willReturn(false);  // Simulate failure

        $webhook = new Webhook("https://webhook-test.info1100.workers.dev/fail1", 2, "Kumaran Powell", "Serene Sands");
        $this->processor->process($webhook);

        $this->assertEquals(5, $webhook->getRetryCount());
        $this->assertTrue($webhook->hasExceededMaxRetries());
    }

    public function testRetryDelayCappedAt60Seconds()
    {
        $this->webhookSender->method('send')->willReturn(false);

        $webhook = new Webhook("https://webhook-test.info1100.workers.dev/fail1", 2, "Kumaran Powell", "Serene Sands");
        $this->mockSleep(); // Simulate the sleep behavior
        $this->processor->process($webhook);

        // Assertions for > 60s
    }

    public function testSkipWebhooksAfterMaxFailures()
    {
        $this->webhookSender->method('send')->willReturn(false);

        $webhooks = [
            new Webhook("https://webhook-test.info1100.workers.dev/fail1", 2, "Kumaran Powell", "Serene Sands"),
            new Webhook("https://webhook-test.info1100.workers.dev/fail1", 5, "Suada Katz", "Serene Sands"),
        ];

        foreach ($webhooks as $webhook) {
            $this->processor->process($webhook);
        }

        $this->assertEquals(5, $webhooks[0]->getRetryCount());
    }

    private function mockSleep()
    {
        $this->getMockBuilder(WebhookProcessor::class)
            ->setMethods(['sleep'])
            ->getMock()
            ->method('sleep')
            ->will($this->returnCallback(function($seconds) {
                // Simulate the sleep time here
                echo "Slept for $seconds seconds\n";
            }));
    }
}
