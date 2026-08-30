<?php

namespace App\Observers;

use App\Pricing\PricingConfiguration;

class PricingCacheObserver
{
    public function saved(mixed $model): void
    {
        PricingConfiguration::forget();
    }

    public function deleted(mixed $model): void
    {
        PricingConfiguration::forget();
    }
}
