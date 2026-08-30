<?php

namespace App\Pricing\Data;

use App\Enums\PricingAdjustmentType;
use App\Enums\PricingLineItemKind;
use App\Pricing\PriceAdjustment;

final readonly class CalculationLineItem
{
    public function __construct(
        public string $key,
        public string $label,
        public PricingLineItemKind $kind,
        public PricingAdjustmentType $adjustmentType,
        public PriceAdjustment $amount,
        public ?string $detail = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toSnapshotArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'kind' => $this->kind->value,
            'adjustment_type' => $this->adjustmentType->value,
            'amount' => $this->amount->toArray(),
            'detail' => $this->detail,
        ];
    }
}
