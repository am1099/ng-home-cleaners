<?php

namespace App\Enums;

enum ConditionFlag: string
{
    case HeavyLimescale = 'heavy_limescale';
    case Mould = 'mould';
    case Pets = 'pets';
    case HeavyGrease = 'heavy_grease';
    case Clutter = 'clutter';
    case NotCleanedInMonths = 'not_cleaned_in_months';

    public function label(): string
    {
        return match ($this) {
            self::HeavyLimescale => 'Heavy limescale',
            self::Mould => 'Mould or damp patches',
            self::Pets => 'Pets in the home',
            self::HeavyGrease => 'Heavy kitchen grease',
            self::Clutter => 'Cluttered surfaces',
            self::NotCleanedInMonths => 'Not cleaned in months',
        };
    }
}
