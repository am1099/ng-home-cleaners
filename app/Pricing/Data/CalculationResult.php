<?php

namespace App\Pricing\Data;

use App\Pricing\Money;
use App\Pricing\PriceRange;

final readonly class CalculationResult
{
    /**
     * @param  list<CalculationLineItem>  $lineItems
     * @param  list<CalculationLineItem>  $modifiers
     * @param  list<CalculationLineItem>  $extras
     */
    public function __construct(
        public PriceRange $baseSubtotal,
        public array $lineItems,
        public array $modifiers,
        public array $extras,
        public PriceRange $calculatedSubtotal,
        public ?CalculationLineItem $minimumAdjustment,
        public ?CalculationLineItem $rangeAdjustment,
        public PriceRange $finalRange,
        public ?int $finalSinglePricePence,
        public string $displayHeadline,
        public string $displayDetail,
        public bool $isNumericEstimate,
        public array $snapshot,
    ) {}

    public function formattedFinalRange(): string
    {
        return Money::formatPenceRange(
            $this->finalRange->minPence,
            $this->finalRange->maxPence,
        );
    }
}
