<?php

namespace App\Infrastructure\Http;

use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Domain\Webhook\Webhook;

class WebhookSender
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function send(Webhook $webhook): bool
    {
        try {
            $response = $this->httpClient->request('POST', $webhook->getUrl(), [
                'json' => $webhook->getPayload(),
            ]);

            if ($response->getStatusCode() === 200) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        } catch (TransportExceptionInterface $e) {
        }
    }
}
