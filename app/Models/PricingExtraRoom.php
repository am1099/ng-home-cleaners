<?php

namespace App\Models;

use App\Enums\RoomModifierType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id',
    'room_type',
    'label',
    'min_pence',
    'max_pence',
    'sort_order',
])]
class PricingExtraRoom extends Model
{
    protected function casts(): array
    {
        return [
            'room_type' => RoomModifierType::class,
            'min_pence' => 'integer',
            'max_pence' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
