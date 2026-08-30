<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettingsService
{
    public const CACHE_KEY = 'site.settings.attributes';

    /** @deprecated Legacy keys cleared on forget/upgrade. */
    public const CACHE_KEY_RECORD = 'site.settings.record';

    /** @deprecated Legacy keys cleared on forget/upgrade. */
    public const CACHE_KEY_ID = 'site.settings.id';

    private ?SiteSetting $resolved = null;

    public function get(): SiteSetting
    {
        if ($this->resolved instanceof SiteSetting) {
            return $this->resolved;
        }

        $cached = Cache::get(self::CACHE_KEY);

        if (is_array($cached) && $cached !== []) {
            return $this->resolved = $this->hydrate($cached);
        }

        // Drop corrupt / legacy payloads (model instances, incomplete classes, bare IDs).
        $this->forget();

        $setting = SiteSetting::query()->first() ?? SiteSetting::instance();

        Cache::forever(self::CACHE_KEY, $setting->getAttributes());

        return $this->resolved = $setting;
    }

    public function forget(): void
    {
        $this->resolved = null;
        Cache::forget(self::CACHE_KEY);
        Cache::forget(self::CACHE_KEY_RECORD);
        Cache::forget(self::CACHE_KEY_ID);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function hydrate(array $attributes): SiteSetting
    {
        $setting = (new SiteSetting)->newFromBuilder($attributes);
        $setting->exists = true;

        return $setting;
    }
}
