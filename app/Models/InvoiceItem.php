<?php

namespace App\Models;

use App\Pricing\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'invoice_id',
    'description',
    'quantity',
    'unit_price_pence',
    'line_total_pence',
    'sort_order',
])]
class InvoiceItem extends Model
{
    protected static function booted(): void
    {
        static::saving(function (InvoiceItem $item): void {
            $item->line_total_pence = self::calculateLineTotalPence(
                $item->quantity ?? 0,
                (int) ($item->unit_price_pence ?? 0),
            );
        });
    }

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'unit_price_pence' => 'integer',
            'line_total_pence' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function unitPriceDisplay(): string
    {
        return Money::formatPenceExact((int) $this->unit_price_pence);
    }

    public function lineTotalDisplay(): string
    {
        return Money::formatPenceExact((int) $this->line_total_pence);
    }

    public static function calculateLineTotalPence(string|float|int $quantity, int $unitPricePence): int
    {
        return (int) round((float) bcmul((string) $quantity, (string) $unitPricePence, 4));
    }
}
