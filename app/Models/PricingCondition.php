<?php

namespace App\Models;

use App\Enums\ConditionFlag;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'service_id',
    'condition_flag',
    'min_pence',
    'max_pence',
])]
class PricingCondition extends Model
{
    protected function casts(): array
    {
        return [
            'condition_flag' => ConditionFlag::class,
            'min_pence' => 'integer',
            'max_pence' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
