<?php

namespace App\Observers;

use App\Models\SiteSetting;
use App\Services\SeoService;
use App\Services\SiteSettingsService;

class SiteSettingObserver
{
    public function saved(SiteSetting $siteSetting): void
    {
        app(SiteSettingsService::class)->forget();
        SeoService::forgetSitemap();
    }
}
