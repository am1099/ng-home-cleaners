<?php

namespace App\Models;

use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Pricing\Money;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'reference',
    'customer_id',
    'service_id',
    'source',
    'status',
    'first_name',
    'last_name',
    'phone',
    'email',
    'postcode',
    'address_line1',
    'address_line2',
    'city',
    'parking_notes',
    'access_notes',
    'preferred_date',
    'arrival_window',
    'frequency',
    'property_type',
    'bedrooms',
    'split_level_flat',
    'floors',
    'bathrooms',
    'wcs',
    'kitchens',
    'reception_rooms',
    'extra_rooms',
    'property_status',
    'condition_flags',
    'condition_notes',
    'addon_ids',
    'selections_snapshot',
    'pricing_snapshot',
    'guide_estimate_headline',
    'guide_estimate_detail',
    'guide_estimate_min_pence',
    'guide_estimate_max_pence',
    'guide_single_price_pence',
    'is_numeric_estimate',
    'final_quote_amount_pence',
    'internal_notes',
    'whatsapp_initiated_at',
    'contacted_at',
    'quote_sent_at',
    'won_at',
    'lost_at',
    'submitted_at',
])]
class QuoteRequest extends Model
{
    protected static function booted(): void
    {
        static::updating(function (QuoteRequest $lead): void {
            if (! $lead->isDirty('status')) {
                return;
            }

            $lead->recordStatusTimestamp($lead->status);
        });
    }

    protected function casts(): array
    {
        return [
            'source' => QuoteRequestSource::class,
            'status' => QuoteRequestStatus::class,
            'preferred_date' => 'date',
            'split_level_flat' => 'boolean',
            'extra_rooms' => 'array',
            'condition_flags' => 'array',
            'addon_ids' => 'array',
            'selections_snapshot' => 'array',
            'pricing_snapshot' => 'array',
            'is_numeric_estimate' => 'boolean',
            'whatsapp_initiated_at' => 'datetime',
            'contacted_at' => 'datetime',
            'quote_sent_at' => 'datetime',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
            'submitted_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function booking(): HasOne
    {
        return $this->hasOne(Booking::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }

    public function finalQuoteDisplay(): ?string
    {
        if ($this->final_quote_amount_pence === null) {
            return null;
        }

        return Money::formatPence((int) $this->final_quote_amount_pence);
    }

    public function recordStatusTimestamp(QuoteRequestStatus $status): void
    {
        match ($status) {
            QuoteRequestStatus::Contacted => $this->contacted_at ??= now(),
            QuoteRequestStatus::QuoteSent => $this->forceFill([
                'contacted_at' => $this->contacted_at ?? now(),
                'quote_sent_at' => $this->quote_sent_at ?? now(),
            ]),
            QuoteRequestStatus::Won => $this->forceFill([
                'contacted_at' => $this->contacted_at ?? now(),
                'quote_sent_at' => $this->quote_sent_at ?? now(),
                'won_at' => $this->won_at ?? now(),
            ]),
            QuoteRequestStatus::Lost => $this->forceFill([
                'contacted_at' => $this->contacted_at ?? now(),
                'lost_at' => $this->lost_at ?? now(),
            ]),
            QuoteRequestStatus::New => null,
        };
    }

    public function markStatus(QuoteRequestStatus $status): void
    {
        $this->status = $status;
        $this->recordStatusTimestamp($status);
        $this->save();
    }
}
