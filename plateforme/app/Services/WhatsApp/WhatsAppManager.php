<?php

namespace App\Services\WhatsApp;

use InvalidArgumentException;

/**
 * Picks the channel for each message: the free gateway as the main channel,
 * the official API for a small share of messages and as a fallback when the
 * main channel fails (for instance if the number gets blocked).
 */
class WhatsAppManager
{
    /**
     * Send the message and return the name of the channel that delivered it.
     *
     * @throws WhatsAppException when every channel failed
     */
    public function send(string $number, string $message): string
    {
        $channels = $this->channelsFor();
        $lastError = null;

        foreach ($channels as $channel) {
            try {
                $channel->send($number, $message);

                return $channel->name();
            } catch (WhatsAppException $exception) {
                if ($exception->isInvalidNumber()) {
                    throw $exception;
                }

                $lastError = $exception;
            }
        }

        throw $lastError ?? new WhatsAppException('Aucun canal WhatsApp configuré.');
    }

    /**
     * @return list<WhatsAppSender>
     */
    private function channelsFor(): array
    {
        $primary = config('services.whatsapp.driver');
        $fallback = config('services.whatsapp.fallback');
        $officialShare = (int) config('services.whatsapp.official_share');

        if ($fallback && $officialShare > 0 && random_int(1, 100) <= $officialShare) {
            [$primary, $fallback] = [$fallback, $primary];
        }

        return collect([$primary, $fallback])
            ->filter()
            ->unique()
            ->map(fn (string $driver) => $this->driver($driver))
            ->values()
            ->all();
    }

    public function driver(string $name): WhatsAppSender
    {
        return match ($name) {
            'gateway' => new GatewaySender,
            'cloud' => new CloudApiSender,
            'log' => new LogSender,
            default => throw new InvalidArgumentException("Canal WhatsApp inconnu : {$name}"),
        };
    }
}
