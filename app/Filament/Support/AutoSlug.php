<?php

namespace App\Filament\Support;

use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Str;

final class AutoSlug
{
    /**
     * Keep slug in sync with the name while the slug is blank or still matches
     * the previous auto-generated value (so manual slug edits are preserved).
     */
    public static function fromName(string $nameField = 'name', string $slugField = 'slug'): \Closure
    {
        return function (?string $state, Set $set, Get $get, ?string $old) use ($slugField): void {
            $currentSlug = (string) ($get($slugField) ?? '');
            $previousAuto = Str::slug((string) ($old ?? ''));

            if ($currentSlug === '' || $currentSlug === $previousAuto) {
                $set($slugField, Str::slug((string) ($state ?? '')));
            }
        };
    }
}
