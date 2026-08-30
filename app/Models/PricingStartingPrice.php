<?php

namespace App\Models;

use App\Enums\PropertyType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id',
    'property_type',
    'min_pence',
    'max_pence',
])]
class PricingStartingPrice extends Model
{
    protected function casts(): array
    {
        return [
            'property_type' => PropertyType::class,
            'min_pence' => 'integer',
            'max_pence' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
