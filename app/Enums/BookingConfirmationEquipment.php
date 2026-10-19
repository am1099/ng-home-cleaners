<?php

namespace App\Enums;

enum BookingConfirmationEquipment: string
{
    case CustomerProvides = 'customer_provides';
    case BringOwn = 'bring_own';

    public function label(): string
    {
        return match ($this) {
            self::CustomerProvides => 'I have a hoover and mop you can use',
            self::BringOwn => 'Please bring your own',
        };
    }
}
