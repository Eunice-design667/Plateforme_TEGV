<?php

namespace App\Rules;

use App\Support\PhoneNumber;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * A phone number WhatsApp can reach: 8 local digits (Burkina Faso) or an international number.
 */
class WhatsAppNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isWellFormed = is_string($value) && preg_match('/^[+0-9 ().-]+$/', $value) === 1;

        if (! $isWellFormed || PhoneNumber::normalize($value) === null) {
            $fail('Le numéro WhatsApp n’est pas valide. Exemple : 70 26 83 95 ou +226 70 26 83 95.');
        }
    }
}
