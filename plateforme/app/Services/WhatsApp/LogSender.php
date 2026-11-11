<?php

namespace App\Services\WhatsApp;

use Illuminate\Support\Facades\Log;

/**
 * Development driver: writes messages to the log instead of sending them.
 */
class LogSender implements WhatsAppSender
{
    public function send(string $number, string $message): void
    {
        Log::info("[WhatsApp] → +{$number}", ['message' => $message]);
    }

    public function name(): string
    {
        return 'log';
    }
}
