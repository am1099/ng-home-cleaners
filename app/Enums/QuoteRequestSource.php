<?php

namespace App\Enums;

enum QuoteRequestSource: string
{
    case Web = 'web';
    case Whatsapp = 'whatsapp';
    case Phone = 'phone';
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Website',
            self::Whatsapp => 'WhatsApp',
            self::Phone => 'Phone',
            self::Manual => 'Manual',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Web => 'primary',
            self::Whatsapp => 'success',
            self::Phone => 'warning',
            self::Manual => 'gray',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $source) => [$source->value => $source->label()],
        )->all();
    }

    /**
     * @return array<string, string>
     */
    public static function manualOptions(): array
    {
        return [
            self::Phone->value => self::Phone->label(),
            self::Manual->value => self::Manual->label(),
        ];
    }
}
