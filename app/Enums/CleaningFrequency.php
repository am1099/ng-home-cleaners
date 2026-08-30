<?php

namespace App\Enums;

enum CleaningFrequency: string
{
    case OneOff = 'one_off';
    case Weekly = 'weekly';
    case Fortnightly = 'fortnightly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match ($this) {
            self::OneOff => 'One-off',
            self::Weekly => 'Weekly',
            self::Fortnightly => 'Fortnightly',
            self::Monthly => 'Monthly',
        };
    }
}
