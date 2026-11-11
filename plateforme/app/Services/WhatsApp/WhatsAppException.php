<?php

namespace App\Services\WhatsApp;

use RuntimeException;

class WhatsAppException extends RuntimeException
{
    /**
     * The number itself is wrong: retrying with another channel will not help.
     */
    public static function invalidNumber(string $number): self
    {
        return new self("Numéro WhatsApp invalide : {$number}", 422);
    }

    public function isInvalidNumber(): bool
    {
        return $this->getCode() === 422;
    }
}
