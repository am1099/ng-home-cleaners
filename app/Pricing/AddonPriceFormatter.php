<?php

namespace App\Pricing;

use App\Models\Addon;
use App\Pricing\Data\EstimateInput;

final class AddonPriceFormatter
{
    public function __construct(
        private readonly PricingEngine $engine,
    ) {}

    public function displayLabel(Addon $addon, ?EstimateInput $context = null): string
    {
        $contribution = $this->engine->calculateAddonContribution($addon, $context);
        $prefix = $addon->show_from_prefix ? 'from ' : '';

        $amount = Money::formatPenceRange(
            $contribution->minPence,
            $contribution->maxPence,
        );

        if ($addon->pricing_unit->value === 'per_bathroom' && $context === null) {
            return $addon->label.' · '.$prefix.$amount.' per bathroom';
        }

        return $addon->label.' · '.$prefix.$amount;
    }

    public function formattedStoredRange(Addon $addon): string
    {
        return Money::formatPenceRange($addon->priceMinPence(), $addon->priceMaxPence());
    }
}
