<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'slug',
    'title',
    'content',
    'seo_title',
    'seo_description',
    'is_published',
])]
class LegalPage extends Model
{
    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function resolveRouteBinding($value, $field = null): self
    {
        return static::query()
            ->published()
            ->where($field ?? 'slug', $value)
            ->firstOrFail();
    }
}
