<?php

namespace App\Enums;

enum AccessOption: string
{
    case SomeoneHome = 'someone_home';
    case KeySafe = 'key_safe';
    case Neighbour = 'neighbour';
    case Concierge = 'concierge';
    case Lockbox = 'lockbox';
    case EntryCode = 'entry_code';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::SomeoneHome => 'Someone will be home',
            self::KeySafe => 'Key safe',
            self::Neighbour => 'Key with a neighbour',
            self::Concierge => 'Concierge / reception',
            self::Lockbox => 'Lockbox',
            self::EntryCode => 'Entry or alarm code',
            self::Other => 'Other',
        };
    }

    /**
     * @return list<string>
     */
    public static function labels(): array
    {
        return array_map(fn (self $option) => $option->label(), self::cases());
    }
}
