<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id',
    'bedrooms_included',
    'extra_min_pence',
    'extra_max_pence',
])]
class PricingBedroomRule extends Model
{
    protected function casts(): array
    {
        return [
            'bedrooms_included' => 'integer',
            'extra_min_pence' => 'integer',
            'extra_max_pence' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
