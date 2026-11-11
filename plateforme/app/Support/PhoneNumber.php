<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalize a WhatsApp number to international digits only (e.g. 22670268395).
     * Eight-digit numbers are treated as Burkina Faso numbers.
     */
    public static function normalize(?string $number): ?string
    {
        if ($number === null) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $number) ?? '';

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (strlen($digits) === 8) {
            $digits = config('tegv.default_country_code').$digits;
        }

        return strlen($digits) >= 10 && strlen($digits) <= 15 ? $digits : null;
    }

    public static function whatsappLink(?string $number, string $text = ''): ?string
    {
        $normalized = self::normalize($number);

        if ($normalized === null) {
            return null;
        }

        return 'https://wa.me/'.$normalized.($text !== '' ? '?text='.rawurlencode($text) : '');
    }

    /**
     * Human readable form: +226 70 26 83 95.
     */
    public static function format(?string $number): ?string
    {
        $normalized = self::normalize($number);

        if ($normalized === null) {
            return $number;
        }

        $country = substr($normalized, 0, strlen($normalized) - 8);
        $local = implode(' ', str_split(substr($normalized, -8), 2));

        return "+{$country} {$local}";
    }
}
