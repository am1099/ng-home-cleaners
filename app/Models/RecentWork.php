<?php

namespace App\Models;

use App\Models\Concerns\HasPublishedScope;
use Database\Factories\RecentWorkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'before_image_path',
    'after_image_path',
    'title',
    'description',
    'alt_text_before',
    'alt_text_after',
    'sort_order',
    'is_published',
    'published_at',
])]
class RecentWork extends Model
{
    /** @use HasFactory<RecentWorkFactory> */
    use HasFactory, HasPublishedScope;

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }
}
