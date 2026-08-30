<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case Empty = 'empty';
    case Furnished = 'furnished';
    case PartFurnished = 'part_furnished';

    public function label(): string
    {
        return match ($this) {
            self::Empty => 'Empty',
            self::Furnished => 'Furnished',
            self::PartFurnished => 'Part-furnished',
        };
    }
}
