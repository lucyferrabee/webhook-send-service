<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Application\Webhook\WebhookProcessor;
use App\Domain\Webhook\Webhook;

class ProcessWebhooksCommand extends Command
{
    protected static $defaultName = 'app:process-webhooks';
    private WebhookProcessor $processor;

    public function __construct(WebhookProcessor $processor)
    {
        $this->processor = $processor;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Processes pending webhooks from the queue.');
        $this->setName('app:process-webhooks');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $webhooks = $this->loadWebhooks();

        foreach ($webhooks as $webhook) {
            $this->processor->process($webhook);
        }

        return Command::SUCCESS;
    }

    private function loadWebhooks(): array
    {
        $fileContent = file_get_contents('src/Repository/webhooks.txt');
        $webhooksData = explode(PHP_EOL, $fileContent);

        $webhooks = [];
        foreach ($webhooksData as $webhookData) {
            [$url, $id, $payload] = explode(',', $webhookData);
            $webhooks[] = new Webhook($id, $url, $payload);
        }

        return $webhooks;
    }
}
