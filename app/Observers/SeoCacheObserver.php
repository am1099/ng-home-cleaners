<?php

namespace App\Observers;

use App\Services\SeoService;

class SeoCacheObserver
{
    public function saved(mixed $model): void
    {
        SeoService::forgetSitemap();
    }

    public function deleted(mixed $model): void
    {
        SeoService::forgetSitemap();
    }
}
