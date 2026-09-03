<?php

namespace App\Models;

use App\Actions\SendReviewRequest;
use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Enums\PaymentType;
use App\Pricing\Money;
use App\Services\BookingReferenceGenerator;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'reference',
    'customer_id',
    'quote_request_id',
    'service_id',
    'address_line1',
    'address_line2',
    'city',
    'postcode',
    'booking_date',
    'arrival_window',
    'expected_duration_minutes',
    'agreed_price_pence',
    'status',
    'internal_notes',
    'completed_at',
    'review_request_sent_at',
    'cancelled_at',
])]
class Booking extends Model
{
    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            if (blank($booking->reference)) {
                $booking->reference = app(BookingReferenceGenerator::class)->next();
            }
        });

        static::updating(function (Booking $booking): void {
            if (! $booking->isDirty('status')) {
                return;
            }

            $booking->recordStatusTimestamp($booking->status);
        });
    }

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'arrival_window' => ArrivalWindow::class,
            'status' => BookingStatus::class,
            'completed_at' => 'datetime',
            'review_request_sent_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quoteRequest(): BelongsTo
    {
        return $this->belongsTo(QuoteRequest::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_date');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest();
    }

    public function latestInvoice(): HasOne
    {
        return $this->hasOne(Invoice::class)->latestOfMany();
    }

    public function activeInvoice(): ?Invoice
    {
        $invoices = $this->relationLoaded('invoices')
            ? $this->invoices
            : $this->invoices()->get();

        return $invoices->first(fn (Invoice $invoice): bool => ! $invoice->isVoid());
    }

    public function canCreateInvoice(): bool
    {
        return $this->status !== BookingStatus::Cancelled
            && $this->customer_id !== null
            && $this->activeInvoice() === null;
    }

    public function recordStatusTimestamp(BookingStatus $status): void
    {
        match ($status) {
            BookingStatus::Completed => $this->forceFill([
                'completed_at' => $this->completed_at ?? now(),
                'cancelled_at' => null,
            ]),
            BookingStatus::Cancelled => $this->forceFill([
                'cancelled_at' => $this->cancelled_at ?? now(),
            ]),
            BookingStatus::Scheduled => $this->forceFill([
                'completed_at' => null,
                'cancelled_at' => null,
            ]),
        };
    }

    public function markStatus(BookingStatus $status): void
    {
        $this->status = $status;
        $this->recordStatusTimestamp($status);
        $this->save();

        if ($status === BookingStatus::Completed) {
            app(SendReviewRequest::class)->handle($this);
        }
    }

    /** Net money received toward this booking (refunds reduce the total). */
    public function paidPence(): int
    {
        return (int) $this->payments()->sum('amount_pence');
    }

    public function outstandingPence(): int
    {
        return max(0, (int) $this->agreed_price_pence - $this->paidPence());
    }

    public function isOverpaid(): bool
    {
        return $this->paidPence() > (int) $this->agreed_price_pence;
    }

    public function overpaidPence(): int
    {
        return max(0, $this->paidPence() - (int) $this->agreed_price_pence);
    }

    public function agreedDisplay(): string
    {
        return Money::formatPence((int) $this->agreed_price_pence);
    }

    public function paidDisplay(): string
    {
        return Money::formatPence($this->paidPence());
    }

    public function outstandingDisplay(): string
    {
        return Money::formatPence($this->outstandingPence());
    }

    public function fullAddress(): string
    {
        return collect([
            $this->address_line1,
            $this->address_line2,
            $this->city,
            $this->postcode,
        ])->filter()->implode(', ');
    }

    /**
     * Normalize payment amount for storage: refunds are stored as negative pence.
     */
    public static function normalizePaymentAmountPence(PaymentType $type, int $amountPence): int
    {
        return match ($type) {
            PaymentType::Refund => -1 * abs($amountPence),
            PaymentType::Adjustment => $amountPence,
            default => abs($amountPence),
        };
    }
}
