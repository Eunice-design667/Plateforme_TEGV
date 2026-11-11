<?php

namespace App\Services\WhatsApp;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Free, unofficial channel: a small gateway server (see whatsapp-gateway/)
 * connected to the church's dedicated WhatsApp number.
 */
class GatewaySender implements WhatsAppSender
{
    public function send(string $number, string $message): void
    {
        try {
            $response = Http::baseUrl(config('services.whatsapp.gateway.url'))
                ->withToken(config('services.whatsapp.gateway.token'))
                ->connectTimeout(5)
                ->timeout(30)
                ->post('/send', ['number' => $number, 'message' => $message]);
        } catch (ConnectionException $exception) {
            throw new WhatsAppException('Passerelle WhatsApp injoignable : '.$exception->getMessage());
        }

        if ($response->status() === 422) {
            throw WhatsAppException::invalidNumber($number);
        }

        if ($response->failed()) {
            throw new WhatsAppException('Passerelle WhatsApp : erreur '.$response->status().' '.$response->body());
        }
    }

    public function name(): string
    {
        return 'gateway';
    }
}
