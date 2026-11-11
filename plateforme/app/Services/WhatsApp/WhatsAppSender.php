<?php

namespace App\Services\WhatsApp;

interface WhatsAppSender
{
    /**
     * Send a text message to a number in international digits (e.g. 22670268395).
     *
     * @throws WhatsAppException when the message could not be delivered
     */
    public function send(string $number, string $message): void;

    public function name(): string;
}
