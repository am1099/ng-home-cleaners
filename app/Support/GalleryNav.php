<?php

namespace App\Support;

use App\Models\GalleryItem;
use Illuminate\Support\Facades\Cache;

final class GalleryNav
{
    public const CACHE_KEY = 'nav.show_gallery';

    public static function visible(): bool
    {
        return (bool) Cache::remember(self::CACHE_KEY, now()->addHour(), function (): bool {
            return GalleryItem::query()->published()->exists();
        });
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
