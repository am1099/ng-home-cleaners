<?php

namespace App\Support;

final class ArrayState
{
    /**
     * Normalise JSON/array columns that may arrive as a string from legacy rows
     * or when casts are bypassed in Filament infolists.
     *
     * @return list<mixed>
     */
    public static function normalize(mixed $state): array
    {
        if (is_array($state)) {
            return array_values($state);
        }

        if (! is_string($state) || $state === '') {
            return [];
        }

        $decoded = json_decode($state, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return array_values($decoded);
        }

        return [$state];
    }
}
