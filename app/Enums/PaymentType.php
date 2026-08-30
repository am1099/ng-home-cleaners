<?php

namespace App\Enums;

enum PaymentType: string
{
    case Deposit = 'deposit';
    case Balance = 'balance';
    case Full = 'full';
    case Adjustment = 'adjustment';
    case Refund = 'refund';

    public function label(): string
    {
        return match ($this) {
            self::Deposit => 'Deposit',
            self::Balance => 'Balance',
            self::Full => 'Full',
            self::Adjustment => 'Adjustment',
            self::Refund => 'Refund',
        };
    }

    public function isMoneyOut(): bool
    {
        return $this === self::Refund;
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(
            fn (self $type) => [$type->value => $type->label()],
        )->all();
    }
}
