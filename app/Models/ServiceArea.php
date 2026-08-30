<?php

namespace App\Models;

use App\Models\Concerns\HasActiveScope;
use Database\Factories\ServiceAreaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'postcode_label',
    'short_intro',
    'content',
    'coverage_notes',
    'seo_title',
    'seo_description',
    'hero_image',
    'is_active',
    'sort_order',
])]
class ServiceArea extends Model
{
    /** @use HasFactory<ServiceAreaFactory> */
    use HasActiveScope, HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ServiceArea $area): void {
            if (blank($area->slug) && filled($area->name)) {
                $area->slug = Str::slug($area->name);
            }
        });
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ServiceAreaFaq::class)->orderBy('sort_order');
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_area_service');
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function resolveRouteBinding($value, $field = null): self
    {
        return static::query()
            ->active()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }
}
