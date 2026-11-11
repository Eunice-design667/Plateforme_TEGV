<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Official WhatsApp Business Cloud API (paid per conversation started by the church).
 * Messages started by the church must use an approved template; the template
 * receives the generated message as its single body parameter.
 */
class CloudApiSender implements WhatsAppSender
{
    public function send(string $number, string $message): void
    {
        $config = config('services.whatsapp.cloud');

        try {
            $response = Http::baseUrl('https://graph.facebook.com/'.$config['version'])
                ->withToken($config['token'])
                ->connectTimeout(5)
                ->timeout(30)
                ->post("/{$config['phone_number_id']}/messages", [
                    'messaging_product' => 'whatsapp',
                    'to' => $number,
                    'type' => 'template',
                    'template' => [
                        'name' => $config['template'],
                        'language' => ['code' => $config['template_language']],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => [['type' => 'text', 'text' => $message]],
                        ]],
                    ],
                ]);
        } catch (ConnectionException $exception) {
            throw new WhatsAppException('API WhatsApp injoignable : '.$exception->getMessage());
        }

        if ($response->failed()) {
            $code = $response->json('error.code');

            // 131026: the number is not on WhatsApp; 100: invalid parameter (bad number).
            if (in_array($code, [131026, 100], true)) {
                throw WhatsAppException::invalidNumber($number);
            }

            throw new WhatsAppException('API WhatsApp : '.($response->json('error.message') ?? $response->body()));
        }
    }

    public function name(): string
    {
        return 'cloud';
    }
}
