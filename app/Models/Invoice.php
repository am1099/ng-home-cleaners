<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Pricing\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable([
    'booking_id',
    'customer_id',
    'invoice_number',
    'status',
    'issue_date',
    'due_date',
    'currency',
    'customer_name',
    'customer_email',
    'customer_phone',
    'billing_address_line1',
    'billing_address_line2',
    'billing_city',
    'billing_postcode',
    'business_name',
    'business_email',
    'business_phone',
    'business_address',
    'company_legal_name',
    'company_registration_number',
    'vat_registered',
    'vat_number',
    'vat_rate_percent',
    'subtotal_pence',
    'discount_pence',
    'vat_pence',
    'total_pence',
    'notes',
    'payment_terms',
    'payment_instructions',
    'booking_reference',
    'booking_date',
    'service_name',
    'pdf_disk',
    'pdf_path',
    'issued_at',
    'first_sent_at',
    'last_sent_at',
    'paid_at',
    'voided_at',
    'void_reason',
    'created_by',
])]
class Invoice extends Model
{
    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'issue_date' => 'date',
            'due_date' => 'date',
            'booking_date' => 'date',
            'vat_registered' => 'boolean',
            'vat_rate_percent' => 'decimal:2',
            'subtotal_pence' => 'integer',
            'discount_pence' => 'integer',
            'vat_pence' => 'integer',
            'total_pence' => 'integer',
            'issued_at' => 'datetime',
            'first_sent_at' => 'datetime',
            'last_sent_at' => 'datetime',
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(InvoiceDelivery::class)->latest();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isDraft(): bool
    {
        return $this->status === InvoiceStatus::Draft;
    }

    public function isIssuedOrLater(): bool
    {
        return in_array($this->status, [
            InvoiceStatus::Issued,
            InvoiceStatus::Sent,
            InvoiceStatus::Paid,
        ], true);
    }

    public function isEditable(): bool
    {
        return $this->isDraft();
    }

    public function isVoid(): bool
    {
        return $this->status === InvoiceStatus::Void;
    }

    public function isOverdue(): bool
    {
        if ($this->isVoid() || $this->status === InvoiceStatus::Paid || $this->isDraft()) {
            return false;
        }

        if (! $this->due_date instanceof Carbon) {
            return false;
        }

        return $this->due_date->isBefore(now()->startOfDay())
            && $this->outstandingPence() > 0;
    }

    public function paidPence(): int
    {
        $booking = $this->relationLoaded('booking')
            ? $this->booking
            : $this->booking()->first();

        if (! $booking) {
            return 0;
        }

        return $booking->paidPence();
    }

    public function outstandingPence(): int
    {
        return max(0, $this->amountDuePence());
    }

    public function amountDuePence(): int
    {
        return (int) $this->total_pence - $this->paidPence();
    }

    public function totalDisplay(): string
    {
        return Money::formatPenceExact((int) $this->total_pence);
    }

    public function subtotalDisplay(): string
    {
        return Money::formatPenceExact((int) $this->subtotal_pence);
    }

    public function discountDisplay(): string
    {
        return Money::formatPenceExact((int) $this->discount_pence);
    }

    public function vatDisplay(): string
    {
        return Money::formatPenceExact((int) $this->vat_pence);
    }

    public function paidDisplay(): string
    {
        return Money::formatPenceExact($this->paidPence());
    }

    public function outstandingDisplay(): string
    {
        return Money::formatPenceExact($this->outstandingPence());
    }

    public function amountDueDisplay(): string
    {
        return Money::formatPenceExact(max(0, $this->amountDuePence()));
    }

    public function billingAddressDisplay(): string
    {
        return collect([
            $this->billing_address_line1,
            $this->billing_address_line2,
            $this->billing_city,
            $this->billing_postcode,
        ])->filter()->implode(', ');
    }

    public function displayNumber(): string
    {
        return filled($this->invoice_number) ? (string) $this->invoice_number : 'DRAFT';
    }

    public function recalculateTotals(): void
    {
        $items = $this->relationLoaded('items')
            ? $this->items
            : $this->items()->get();

        $subtotal = (int) $items->sum(fn (InvoiceItem $item): int => (int) $item->line_total_pence);
        $discount = max(0, (int) $this->discount_pence);
        $net = max(0, $subtotal - $discount);

        $vat = 0;

        if ($this->vat_registered && filled($this->vat_rate_percent)) {
            $rate = (string) $this->vat_rate_percent;
            $vat = (int) round((float) bcmul((string) $net, bcdiv($rate, '100', 6), 4));
        }

        $this->forceFill([
            'subtotal_pence' => $subtotal,
            'vat_pence' => $vat,
            'total_pence' => $net + $vat,
        ])->save();
    }
}
