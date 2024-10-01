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
        var_dump('trying to send for webhook id: ' . $webhook->getId());

        try {
            $response = $this->httpClient->request('GET', $webhook->getUrl(), [
                'json' => $webhook->getPayload(),
            ]);

            if ($response->getStatusCode() === 200) {
                var_dump('got a true response, moving to next webhook');
                return true;
            }
            var_dump('got a false response for webhook id: ' . $webhook->getId());
            return false;
        } catch (\Exception $e) {
            return false;
        } catch (TransportExceptionInterface $e) {
        }
    }
}
