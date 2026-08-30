<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'condition_uplift_percent',
    'max_condition_uplift_percent',
    'furnished_multiplier',
    'empty_multiplier',
    'weekly_discount_percent',
    'fortnightly_discount_percent',
    'monthly_discount_percent',
    'regular_min_pence',
    'regular_min_max_pence',
    'regular_single_price_bias_percent',
    'rounding_step_pence',
    'included_floors_baseline',
    'range_narrow_percent_per_signal',
    'max_range_narrow_percent',
])]
class PricingSetting extends Model
{
    protected $table = 'pricing_settings';

    protected function casts(): array
    {
        return [
            'condition_uplift_percent' => 'decimal:2',
            'max_condition_uplift_percent' => 'decimal:2',
            'furnished_multiplier' => 'decimal:3',
            'empty_multiplier' => 'decimal:3',
            'weekly_discount_percent' => 'decimal:2',
            'fortnightly_discount_percent' => 'decimal:2',
            'monthly_discount_percent' => 'decimal:2',
            'regular_min_pence' => 'integer',
            'regular_min_max_pence' => 'integer',
            'regular_single_price_bias_percent' => 'decimal:2',
            'rounding_step_pence' => 'integer',
            'included_floors_baseline' => 'integer',
            'range_narrow_percent_per_signal' => 'decimal:2',
            'max_range_narrow_percent' => 'decimal:2',
        ];
    }

    public static function instance(): self
    {
        return static::query()->firstOrCreate([]);
    }
}
