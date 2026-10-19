<?php

namespace App\Models;

use App\Enums\BookingConfirmationEquipment;
use App\Enums\BookingConfirmationParking;
use App\Enums\BookingConfirmationStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'full_name',
    'address',
    'access_instructions',
    'parking',
    'equipment_preference',
    'notes',
    'valuables_confirmed_at',
    'payment_confirmed_at',
    'customer_id',
    'booking_id',
    'status',
    'ip_address',
    'submitted_at',
    'reviewed_at',
])]
class BookingConfirmation extends Model
{
    protected function casts(): array
    {
        return [
            'parking' => BookingConfirmationParking::class,
            'equipment_preference' => BookingConfirmationEquipment::class,
            'status' => BookingConfirmationStatus::class,
            'valuables_confirmed_at' => 'datetime',
            'payment_confirmed_at' => 'datetime',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
