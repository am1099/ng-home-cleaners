<?php

namespace App\Support;

final class UkContactNormalizer
{
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', trim($phone));

        if (str_starts_with($digits, '44') && strlen($digits) > 2) {
            $digits = '0'.substr($digits, 2);
        }

        return $digits;
    }

    public static function formatPhoneDisplay(string $phone): string
    {
        $digits = self::normalizePhone($phone);

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 0, 5).' '.substr($digits, 5);
        }

        return trim($phone);
    }

    public static function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }

    public static function normalizePostcode(string $postcode): string
    {
        $compact = strtoupper(preg_replace('/\s+/', '', trim($postcode)));

        if (strlen($compact) < 5) {
            return $compact;
        }

        return substr($compact, 0, -3).' '.substr($compact, -3);
    }
}
