<?php

namespace App\Support;

final class UkPostcode
{
    /**
     * Extract the outward code / district (e.g. NG1 from "NG1 1AA").
     */
    public static function district(?string $postcode): ?string
    {
        if (blank($postcode)) {
            return null;
        }

        $spaced = strtoupper(trim((string) preg_replace('/\s+/', ' ', trim($postcode))));

        if (preg_match('/^([A-Z]{1,2}\d{1,2}[A-Z]?)\s+\d[A-Z]{2}$/', $spaced, $matches) === 1) {
            return $matches[1];
        }

        $compact = preg_replace('/\s+/', '', $spaced) ?? '';

        if (strlen($compact) >= 5) {
            return substr($compact, 0, -3);
        }

        if (preg_match('/^([A-Z]{1,2}\d{1,2}[A-Z]?)$/', $compact, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
