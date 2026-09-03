<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'first_name',
    'last_name',
    'phone_normalized',
    'phone_display',
    'email',
    'postcode',
    'address_line1',
    'address_line2',
    'city',
    'notes',
])]
class Customer extends Model
{
    public function quoteRequests(): HasMany
    {
        return $this->hasMany(QuoteRequest::class)->latest('submitted_at');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->latest('booking_date');
    }

    public function payments(): HasMany
    {
        return $this->hasManyThrough(Payment::class, Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest();
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
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
}
