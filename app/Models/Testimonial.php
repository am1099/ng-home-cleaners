<?php

namespace App\Models;

use App\Models\Concerns\HasPublishedScope;
use Database\Factories\TestimonialFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'customer_name',
    'rating',
    'review',
    'location',
    'service_id',
    'source',
    'source_url',
    'is_published',
    'published_at',
    'is_demo',
    'sort_order',
])]
class Testimonial extends Model
{
    /** @use HasFactory<TestimonialFactory> */
    use HasFactory, HasPublishedScope;

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
            'is_demo' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function scopePublishedForProduction(Builder $query): Builder
    {
        return $query->published()->where('is_demo', false);
    }
}
