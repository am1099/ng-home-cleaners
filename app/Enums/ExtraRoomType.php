<?php

namespace App\Enums;

enum ExtraRoomType: string
{
    case Conservatory = 'conservatory';
    case Office = 'office';
    case Utility = 'utility';
    case Loft = 'loft';

    public function label(): string
    {
        return match ($this) {
            self::Conservatory => 'Conservatory',
            self::Office => 'Office',
            self::Utility => 'Utility room',
            self::Loft => 'Loft room',
        };
    }
}
