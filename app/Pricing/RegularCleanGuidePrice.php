<?php

namespace App\Pricing;

use App\Enums\CleaningFrequency;
use App\Enums\PropertyType;
use App\Models\Service;
use App\Pricing\Data\EstimateInput;

/**
 * Marketing and default calculator guide for a 1-bed, 1-bath regular clean.
 * Always derived from PricingEngine — never hard-coded in the UI.
 */
final class RegularCleanGuidePrice
{
    public function __construct(
        private readonly PricingEngine $engine,
    ) {}

    public function pence(): ?int
    {
        $service = Service::query()
            ->active()
            ->where('slug', 'regular-clean')
            ->first();

        if (! $service) {
            return null;
        }

        try {
            $result = $this->engine->calculate(new EstimateInput(
                service: $service,
                propertyType: PropertyType::House,
                bedrooms: 1,
                bathrooms: 1,
                frequency: CleaningFrequency::OneOff,
            ));
        } catch (\Throwable) {
            return null;
        }

        return $result->finalSinglePricePence;
    }

    public function formatted(): ?string
    {
        $pence = $this->pence();

        return $pence === null ? null : Money::formatPence($pence);
    }
}
