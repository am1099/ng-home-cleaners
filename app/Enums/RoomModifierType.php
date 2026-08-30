<?php

namespace App\Enums;

enum RoomModifierType: string
{
    case Bathroom = 'bathroom';
    case Wc = 'wc';
    case Kitchen = 'kitchen';
    case Reception = 'reception';
    case Floor = 'floor';
    case ExtraRoom = 'extra_room';

    public function label(): string
    {
        return match ($this) {
            self::Bathroom => 'Extra bathroom',
            self::Wc => 'Separate toilet (WC)',
            self::Kitchen => 'Extra kitchen',
            self::Reception => 'Extra reception room',
            self::Floor => 'Extra floor',
            self::ExtraRoom => 'Extra room (conservatory, office, etc.)',
        };
    }
}
