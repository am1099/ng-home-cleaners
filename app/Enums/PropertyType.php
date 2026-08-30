<?php

namespace App\Enums;

enum PropertyType: string
{
    case Flat = 'flat';
    case House = 'house';
    case Bungalow = 'bungalow';

    public function label(): string
    {
        return match ($this) {
            self::Flat => 'Flat',
            self::House => 'House',
            self::Bungalow => 'Bungalow',
        };
    }

    public function pricingPropertyType(): self
    {
        return $this;
    }

    public function defaultFloors(): int
    {
        return match ($this) {
            self::Flat, self::Bungalow => 1,
            self::House => 2,
        };
    }
}
