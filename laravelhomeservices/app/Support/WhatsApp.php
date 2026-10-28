<?php

declare(strict_types=1);

namespace App\Support;

final class WhatsApp
{
    /**
     * Normalise an Ecuadorian phone (e.g. "099 552 8556" or "+593 99...") to wa.me format.
     */
    public static function normalize(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if ($digits === null || $digits === '') {
            return null;
        }

        $countryCode = (string) config('marketplace.whatsapp_country_code');

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '0')) {
            $digits = $countryCode . substr($digits, 1);
        } elseif (! str_starts_with($digits, $countryCode) && strlen($digits) === 9) {
            $digits = $countryCode . $digits;
        }

        return strlen($digits) >= 10 ? $digits : null;
    }

    public static function link(?string $phone, string $message = ''): ?string
    {
        $number = self::normalize($phone);

        if ($number === null) {
            return null;
        }

        return 'https://wa.me/' . $number . ($message === '' ? '' : '?text=' . rawurlencode($message));
    }
}
