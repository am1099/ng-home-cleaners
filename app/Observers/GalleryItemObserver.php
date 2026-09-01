<?php

namespace App\Observers;

use App\Models\GalleryItem;
use App\Support\GalleryNav;

class GalleryItemObserver
{
    public function saved(GalleryItem $item): void
    {
        GalleryNav::forget();
    }

    public function deleted(GalleryItem $item): void
    {
        GalleryNav::forget();
    }
}
