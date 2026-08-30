<?php

namespace App\Enums;

enum ArrivalWindow: string
{
    case Morning = 'morning';
    case Afternoon = 'afternoon';
    case Flexible = 'flexible';

    public function label(): string
    {
        return match ($this) {
            self::Morning => 'Morning, from 9am',
            self::Afternoon => 'Afternoon, from 1pm',
            self::Flexible => 'Flexible',
        };
    }

    public function shortLabel(): string
    {
        return match ($this) {
            self::Morning => 'Morning',
            self::Afternoon => 'Afternoon',
            self::Flexible => 'Flexible',
        };
    }

    public function conflictsWith(self $other): bool
    {
        if ($this === self::Flexible || $other === self::Flexible) {
            return true;
        }

        return $this === $other;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $window) => [$window->value => $window->label()],
        )->all();
    }
}
