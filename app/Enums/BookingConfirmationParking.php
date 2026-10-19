<?php

namespace App\Enums;

enum BookingConfirmationParking: string
{
    case OnSite = 'on_site';
    case PaidOrPermit = 'paid_or_permit';
    case None = 'none';

    public function label(): string
    {
        return match ($this) {
            self::OnSite => 'Yes, there is parking on site',
            self::PaidOrPermit => 'Paid or permit parking nearby',
            self::None => 'No parking available',
        };
    }
}
