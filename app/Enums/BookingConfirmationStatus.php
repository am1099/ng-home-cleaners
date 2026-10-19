<?php

namespace App\Enums;

enum BookingConfirmationStatus: string
{
    case New = 'new';
    case Reviewed = 'reviewed';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Reviewed => 'Reviewed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Reviewed => 'success',
        };
    }
}
