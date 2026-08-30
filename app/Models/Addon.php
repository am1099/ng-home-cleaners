<?php

namespace App\Models;

use App\Enums\AddonPricingUnit;
use App\Models\Concerns\HasActiveScope;
use App\Pricing\AddonPriceFormatter;
use Database\Factories\AddonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'label',
    'description',
    'disclaimer',
    'price_pence',
    'price_max_pence',
    'pricing_unit',
    'show_from_prefix',
    'is_active',
    'sort_order',
])]
class Addon extends Model
{
    /** @use HasFactory<AddonFactory> */
    use HasActiveScope, HasFactory;

    protected function casts(): array
    {
        return [
            'price_pence' => 'integer',
            'price_max_pence' => 'integer',
            'pricing_unit' => AddonPricingUnit::class,
            'show_from_prefix' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Addon $addon): void {
            if (blank($addon->slug) && filled($addon->name)) {
                $addon->slug = Str::slug($addon->name);
            }
        });
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    public function priceMinPence(): int
    {
        return $this->price_pence;
    }

    public function priceMaxPence(): int
    {
        return $this->price_max_pence ?? $this->price_pence;
    }

    public function formattedPrice(): string
    {
        return app(AddonPriceFormatter::class)->displayLabel($this);
    }
}
