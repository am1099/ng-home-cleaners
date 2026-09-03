<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Pricing\Money;
use App\Services\InvoiceBalanceService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'booking_id',
    'amount_pence',
    'type',
    'method',
    'paid_date',
    'reference',
    'notes',
])]
class Payment extends Model
{
    protected static function booted(): void
    {
        static::saving(function (Payment $payment): void {
            if ($payment->type instanceof PaymentType || filled($payment->type)) {
                $type = $payment->type instanceof PaymentType
                    ? $payment->type
                    : PaymentType::from((string) $payment->type);

                $payment->amount_pence = Booking::normalizePaymentAmountPence(
                    $type,
                    (int) $payment->amount_pence,
                );
            }
        });

        $syncInvoices = function (Payment $payment): void {
            if (! filled($payment->booking_id)) {
                return;
            }

            app(InvoiceBalanceService::class)->syncForBookingId((int) $payment->booking_id);
        };

        static::saved($syncInvoices);
        static::deleted($syncInvoices);
    }

    protected function casts(): array
    {
        return [
            'type' => PaymentType::class,
            'method' => PaymentMethod::class,
            'paid_date' => 'date',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function amountDisplay(): string
    {
        return Money::formatPence((int) $this->amount_pence);
    }

    public function absoluteAmountDisplay(): string
    {
        return Money::formatPence(abs((int) $this->amount_pence));
    }
}
