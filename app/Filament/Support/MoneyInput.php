<?php

namespace App\Filament\Support;

use Filament\Forms\Components\TextInput;

class MoneyInput
{
    public static function make(string $name = 'price_pence', string $label = 'Price'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->minValue(0)
            ->step(0.01)
            ->prefix('£')
            ->required()
            ->dehydrateStateUsing(fn (?string $state): ?int => filled($state) ? (int) round(((float) $state) * 100) : null)
            ->formatStateUsing(fn (?int $state): ?string => $state !== null ? number_format($state / 100, 2, '.', '') : null);
    }

    public static function signed(string $name = 'amount_pence', string $label = 'Amount'): TextInput
    {
        return TextInput::make($name)
            ->label($label)
            ->numeric()
            ->step(0.01)
            ->prefix('£')
            ->required()
            ->dehydrateStateUsing(fn (?string $state): ?int => filled($state) ? (int) round(((float) $state) * 100) : null)
            ->formatStateUsing(fn (?int $state): ?string => $state !== null ? number_format($state / 100, 2, '.', '') : null);
    }
}
