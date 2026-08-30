<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum AddonPricingUnit: string implements HasLabel
{
    case Flat = 'flat';
    case PerBathroom = 'per_bathroom';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Flat => 'Fixed price',
            self::PerBathroom => 'Per bathroom',
        };
    }

    public function label(): string
    {
        return $this->getLabel() ?? $this->value;
    }
}
