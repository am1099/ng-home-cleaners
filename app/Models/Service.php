<?php

namespace App\Models;

use App\Enums\ServiceIcon;
use App\Models\Concerns\HasActiveScope;
use App\Support\Media;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'name',
    'slug',
    'card_title',
    'short_description',
    'estimate_description',
    'full_description',
    'icon',
    'cta_label',
    'hero_image',
    'card_image',
    'og_image',
    'seo_title',
    'seo_description',
    'is_active',
    'sort_order',
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasActiveScope, HasFactory;

    protected function casts(): array
    {
        return [
            'icon' => ServiceIcon::class,
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Service $service): void {
            if (blank($service->slug) && filled($service->name)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    public function inclusions(): HasMany
    {
        return $this->hasMany(ServiceInclusion::class)->orderBy('sort_order');
    }

    public function exclusions(): HasMany
    {
        return $this->hasMany(ServiceExclusion::class)->orderBy('sort_order');
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(ServiceFaq::class)->orderBy('sort_order');
    }

    public function startingPrices(): HasMany
    {
        return $this->hasMany(PricingStartingPrice::class);
    }

    public function bedroomRule(): HasMany
    {
        return $this->hasMany(PricingBedroomRule::class);
    }

    public function addons(): BelongsToMany
    {
        return $this->belongsToMany(Addon::class);
    }

    public function galleryItems(): HasMany
    {
        return $this->hasMany(GalleryItem::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function recentWorks(): HasMany
    {
        return $this->hasMany(RecentWork::class);
    }

    public function serviceAreas(): BelongsToMany
    {
        return $this->belongsToMany(ServiceArea::class, 'service_area_service');
    }

    public function cardImageUrl(): string
    {
        if (filled($this->card_image)) {
            return Media::url($this->card_image) ?? asset('images/defaults/service-card.jpg');
        }

        return asset('images/defaults/service-card.jpg');
    }

    public function heroImageUrl(): ?string
    {
        if (filled($this->hero_image)) {
            return Media::url($this->hero_image);
        }

        if (filled($this->card_image)) {
            return Media::url($this->card_image);
        }

        return null;
    }

    public function requiresManualQuote(): bool
    {
        return $this->slug === 'office-commercial';
    }

    public function isRegularClean(): bool
    {
        return $this->slug === 'regular-clean';
    }

    public function appliesPropertyStatusMultipliers(): bool
    {
        return in_array($this->slug, ['deep-clean', 'end-of-tenancy'], true);
    }

    /**
     * Tailwind classes for calendar chips (stable per service slug).
     *
     * @return array{bg: string, border: string, text: string, pill: string}
     */
    public function calendarColors(): array
    {
        return match ($this->slug) {
            'regular-clean' => [
                'bg' => 'bg-teal-100 dark:bg-teal-500/20',
                'border' => 'border-teal-400 dark:border-teal-400/50',
                'text' => 'text-teal-950 dark:text-teal-50',
                'pill' => 'bg-teal-700 text-white',
            ],
            'deep-clean' => [
                'bg' => 'bg-violet-100 dark:bg-violet-500/20',
                'border' => 'border-violet-400 dark:border-violet-400/50',
                'text' => 'text-violet-950 dark:text-violet-50',
                'pill' => 'bg-violet-700 text-white',
            ],
            'end-of-tenancy' => [
                'bg' => 'bg-amber-100 dark:bg-amber-500/20',
                'border' => 'border-amber-400 dark:border-amber-400/50',
                'text' => 'text-amber-950 dark:text-amber-50',
                'pill' => 'bg-amber-700 text-white',
            ],
            'office-commercial' => [
                'bg' => 'bg-sky-100 dark:bg-sky-500/20',
                'border' => 'border-sky-400 dark:border-sky-400/50',
                'text' => 'text-sky-950 dark:text-sky-50',
                'pill' => 'bg-sky-700 text-white',
            ],
            default => [
                'bg' => 'bg-gray-100 dark:bg-white/10',
                'border' => 'border-gray-400 dark:border-white/30',
                'text' => 'text-gray-950 dark:text-gray-100',
                'pill' => 'bg-gray-700 text-white',
            ],
        };
    }

    public function resolveRouteBinding($value, $field = null): self
    {
        return static::query()
            ->active()
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->firstOrFail();
    }
}
