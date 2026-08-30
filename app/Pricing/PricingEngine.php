<?php

namespace App\Pricing;

use App\Enums\AddonPricingUnit;
use App\Enums\CleaningFrequency;
use App\Enums\PricingAdjustmentType;
use App\Enums\PricingLineItemKind;
use App\Enums\PropertyStatus;
use App\Enums\RoomModifierType;
use App\Models\Addon;
use App\Pricing\Data\CalculationLineItem;
use App\Pricing\Data\CalculationResult;
use App\Pricing\Data\EstimateInput;
use RuntimeException;

class PricingEngine
{
    public function calculate(EstimateInput $input): CalculationResult
    {
        $config = PricingConfiguration::load();
        $settings = $config->settings;
        $lineItems = [];

        if ($input->service->requiresManualQuote()) {
            return $this->manualQuoteResult($input);
        }

        $propertyType = $input->propertyType;
        $starting = $config->startingPrice($input->service->id, $propertyType);

        if (! $starting) {
            throw new RuntimeException(sprintf(
                'No starting price for service [%s] and property [%s].',
                $input->service->slug,
                $propertyType->value,
            ));
        }

        $range = new PriceRange($starting->min_pence, $starting->max_pence);
        $lineItems[] = new CalculationLineItem(
            key: 'starting_price',
            label: 'Starting price',
            kind: PricingLineItemKind::StartingPrice,
            adjustmentType: PricingAdjustmentType::Fixed,
            amount: new PriceAdjustment($starting->min_pence, $starting->max_pence),
            detail: sprintf(
                '%s · %s · %s bedroom%s included',
                $input->service->name,
                $propertyType->label(),
                $config->bedroomRule($input->service->id)?->bedrooms_included ?? 1,
                ($config->bedroomRule($input->service->id)?->bedrooms_included ?? 1) === 1 ? '' : 's',
            ),
        );

        $baseSubtotal = $range;

        [$range, $bedroomItem] = $this->applyExtraBedrooms($input, $config, $range);
        if ($bedroomItem) {
            $lineItems[] = $bedroomItem;
        }

        [$range, $roomItems] = $this->applyExtraRooms($input, $config, $settings, $range);
        $lineItems = array_merge($lineItems, $roomItems);

        [$range, $conditionItems] = $this->applyConditions($input, $config, $range);
        $lineItems = array_merge($lineItems, $conditionItems);

        [$range, $addonItems] = $this->applyAddons($input, $range);
        $lineItems = array_merge($lineItems, $addonItems);

        [$range, $furnishingItem] = $this->applyFurnishing($input, $settings, $range);
        if ($furnishingItem) {
            $lineItems[] = $furnishingItem;
        }

        $preFrequency = $range;

        [$range, $frequencyItem] = $this->applyFrequency($input, $settings, $range);
        if ($frequencyItem) {
            $lineItems[] = $frequencyItem;
        }

        $rounded = $range->round((int) $settings->rounding_step_pence);

        $finalSingle = null;
        if ($input->service->isRegularClean()) {
            $finalSingle = max((int) $settings->regular_min_pence, $rounded->minPence);
            $displayHeadline = 'From '.Money::formatPence($finalSingle).' a visit';
            $displayDetail = $this->frequencyDetail($input->frequency, $settings);
        } else {
            $displayHeadline = Money::formatPenceRange($rounded->minPence, $rounded->maxPence);
            $displayDetail = 'Guide estimate based on your selections.';
        }

        $snapshot = [
            'engine' => 'simplified_v1',
            'service_id' => $input->service->id,
            'service_slug' => $input->service->slug,
            'property_type' => $propertyType->value,
            'bedrooms' => $input->bedrooms,
            'bathrooms' => $input->bathrooms,
            'wcs' => $input->wcs,
            'kitchens' => $input->kitchens,
            'reception_rooms' => $input->receptionRooms,
            'floors' => $input->floors,
            'extra_room_slugs' => $input->extraRoomSlugs,
            'frequency' => $input->frequency?->value,
            'property_status' => $input->propertyStatus?->value,
            'condition_flags' => array_map(fn ($flag) => $flag->value, $input->conditionFlags),
            'addon_ids' => $input->addonIds,
            'starting_subtotal' => $baseSubtotal->toArray(),
            'pre_frequency_range' => $preFrequency->toArray(),
            'final_range' => $rounded->toArray(),
            'final_single_pence' => $finalSingle,
            'line_items' => array_map(fn (CalculationLineItem $item) => $item->toSnapshotArray(), $lineItems),
        ];

        return new CalculationResult(
            baseSubtotal: $baseSubtotal,
            lineItems: $lineItems,
            modifiers: array_values(array_filter([$bedroomItem, $furnishingItem, $frequencyItem])),
            extras: array_merge($roomItems, $conditionItems, $addonItems),
            calculatedSubtotal: $preFrequency,
            minimumAdjustment: null,
            rangeAdjustment: null,
            finalRange: $rounded,
            finalSinglePricePence: $finalSingle,
            displayHeadline: $displayHeadline,
            displayDetail: $displayDetail,
            isNumericEstimate: true,
            snapshot: $snapshot,
        );
    }

    private function manualQuoteResult(EstimateInput $input): CalculationResult
    {
        $zero = PriceRange::zero();

        return new CalculationResult(
            baseSubtotal: $zero,
            lineItems: [],
            modifiers: [],
            extras: [],
            calculatedSubtotal: $zero,
            minimumAdjustment: null,
            rangeAdjustment: null,
            finalRange: $zero,
            finalSinglePricePence: null,
            displayHeadline: 'Priced per visit',
            displayDetail: 'Commercial premises are quoted after a short walk-round.',
            isNumericEstimate: false,
            snapshot: [
                'engine' => 'simplified_v1',
                'service_id' => $input->service->id,
                'service_slug' => $input->service->slug,
                'manual_quote' => true,
            ],
        );
    }

    /**
     * @return array{0: PriceRange, 1: ?CalculationLineItem}
     */
    private function applyExtraBedrooms(EstimateInput $input, PricingConfiguration $config, PriceRange $range): array
    {
        $rule = $config->bedroomRule($input->service->id);

        if (! $rule) {
            return [$range, null];
        }

        $extra = max(0, $input->bedrooms - $rule->bedrooms_included);

        if ($extra === 0) {
            return [$range, null];
        }

        $min = $extra * $rule->extra_min_pence;
        $max = $extra * $rule->extra_max_pence;
        $range = $range->addFixed($min, $max);

        return [
            $range,
            new CalculationLineItem(
                key: 'extra_bedrooms',
                label: 'Bedrooms',
                kind: PricingLineItemKind::ExtraBedrooms,
                adjustmentType: PricingAdjustmentType::Fixed,
                amount: new PriceAdjustment($min, $max),
                detail: sprintf(
                    '%d additional bedroom%s',
                    $extra,
                    $extra === 1 ? '' : 's',
                ),
            ),
        ];
    }

    /**
     * @return array{0: PriceRange, 1: list<CalculationLineItem>}
     */
    private function applyExtraRooms(
        EstimateInput $input,
        PricingConfiguration $config,
        $settings,
        PriceRange $range,
    ): array {
        $items = [];
        $counts = [
            RoomModifierType::Bathroom->value => max($input->bathrooms - 1, 0),
            RoomModifierType::Wc->value => $input->wcs,
            RoomModifierType::Kitchen->value => max($input->kitchens - 1, 0),
            RoomModifierType::Reception->value => max($input->receptionRooms - 1, 0),
            RoomModifierType::Floor->value => max($input->floors - (int) $settings->included_floors_baseline, 0),
            RoomModifierType::ExtraRoom->value => count($input->extraRoomSlugs),
        ];

        foreach ($counts as $type => $count) {
            if ($count <= 0) {
                continue;
            }

            $roomType = RoomModifierType::from($type);
            $row = $config->extraRoom($input->service->id, $roomType);

            if (! $row) {
                continue;
            }

            $min = $count * $row->min_pence;
            $max = $count * $row->max_pence;
            $range = $range->addFixed($min, $max);

            $items[] = new CalculationLineItem(
                key: 'extra_room_'.$type,
                label: $row->label,
                kind: PricingLineItemKind::ExtraRoom,
                adjustmentType: PricingAdjustmentType::Fixed,
                amount: new PriceAdjustment($min, $max),
                detail: $count > 1 ? '×'.$count : null,
            );
        }

        return [$range, $items];
    }

    /**
     * @return array{0: PriceRange, 1: list<CalculationLineItem>}
     */
    private function applyConditions(EstimateInput $input, PricingConfiguration $config, PriceRange $range): array
    {
        $items = [];

        foreach ($input->conditionFlags as $flag) {
            $row = $config->condition($input->service->id, $flag);

            if (! $row) {
                continue;
            }

            $range = $range->addFixed($row->min_pence, $row->max_pence);

            $items[] = new CalculationLineItem(
                key: 'condition_'.$flag->value,
                label: $flag->label(),
                kind: PricingLineItemKind::Condition,
                adjustmentType: PricingAdjustmentType::Fixed,
                amount: new PriceAdjustment($row->min_pence, $row->max_pence),
            );
        }

        return [$range, $items];
    }

    /**
     * @return array{0: PriceRange, 1: list<CalculationLineItem>}
     */
    private function applyAddons(EstimateInput $input, PriceRange $range): array
    {
        if ($input->addonIds === []) {
            return [$range, []];
        }

        $items = [];
        $addons = $input->service->relationLoaded('addons')
            ? $input->service->addons->whereIn('id', $input->addonIds)->sortBy('sort_order')->values()
            : Addon::query()->active()->whereIn('id', $input->addonIds)->orderBy('sort_order')->get();

        foreach ($addons as $addon) {
            $min = $addon->priceMinPence();
            $max = $addon->priceMaxPence();

            if ($addon->pricing_unit === AddonPricingUnit::PerBathroom) {
                $multiplier = $input->bathroomMultiplier();
                $min *= $multiplier;
                $max *= $multiplier;
            }

            $range = $range->addFixed($min, $max);

            $items[] = new CalculationLineItem(
                key: 'addon_'.$addon->slug,
                label: $addon->label,
                kind: PricingLineItemKind::Addon,
                adjustmentType: PricingAdjustmentType::Fixed,
                amount: new PriceAdjustment($min, $max),
                detail: $addon->pricing_unit === AddonPricingUnit::PerBathroom
                    ? '×'.$input->bathroomMultiplier().' bathroom(s)'
                    : null,
            );
        }

        return [$range, $items];
    }

    /**
     * @return array{0: PriceRange, 1: ?CalculationLineItem}
     */
    private function applyFurnishing(EstimateInput $input, $settings, PriceRange $range): array
    {
        if (! $input->service->appliesPropertyStatusMultipliers() || ! $input->propertyStatus) {
            return [$range, null];
        }

        $percent = match ($input->propertyStatus) {
            PropertyStatus::Empty => '-8',
            PropertyStatus::Furnished => '5',
            PropertyStatus::PartFurnished => '0',
        };

        // Prefer settings multipliers when present (owner may have edited them).
        if ($input->propertyStatus === PropertyStatus::Empty) {
            $factor = (string) $settings->empty_multiplier;
            $percent = bcmul(bcsub($factor, '1', 4), '100', 2);
        } elseif ($input->propertyStatus === PropertyStatus::Furnished) {
            $factor = (string) $settings->furnished_multiplier;
            $percent = bcmul(bcsub($factor, '1', 4), '100', 2);
        } else {
            return [$range, null];
        }

        if ((float) $percent === 0.0) {
            return [$range, null];
        }

        $before = $range;
        if ((float) $percent < 0) {
            $range = new PriceRange(
                Money::applyPercentDecrease($range->minPence, ltrim($percent, '-')),
                Money::applyPercentDecrease($range->maxPence, ltrim($percent, '-')),
            );
        } else {
            $range = new PriceRange(
                Money::applyPercentIncrease($range->minPence, $percent),
                Money::applyPercentIncrease($range->maxPence, $percent),
            );
        }

        $deltaMin = $range->minPence - $before->minPence;
        $deltaMax = $range->maxPence - $before->maxPence;

        return [
            $range,
            new CalculationLineItem(
                key: 'furnishing',
                label: 'Property status',
                kind: PricingLineItemKind::FurnishingAdjustment,
                adjustmentType: PricingAdjustmentType::Percentage,
                amount: new PriceAdjustment($deltaMin, $deltaMax),
                detail: sprintf('%s (%s%%)', $input->propertyStatus->label(), $percent),
            ),
        ];
    }

    /**
     * @return array{0: PriceRange, 1: ?CalculationLineItem}
     */
    private function applyFrequency(EstimateInput $input, $settings, PriceRange $range): array
    {
        if (! $input->service->isRegularClean() || ! $input->frequency) {
            return [$range, null];
        }

        $percent = match ($input->frequency) {
            CleaningFrequency::Weekly => (string) $settings->weekly_discount_percent,
            CleaningFrequency::Fortnightly => (string) $settings->fortnightly_discount_percent,
            CleaningFrequency::Monthly => (string) $settings->monthly_discount_percent,
            CleaningFrequency::OneOff => '0',
        };

        if ((float) $percent == 0.0) {
            return [$range, null];
        }

        $before = $range;
        $range = new PriceRange(
            Money::applyPercentDecrease($range->minPence, $percent),
            Money::applyPercentDecrease($range->maxPence, $percent),
        );

        return [
            $range,
            new CalculationLineItem(
                key: 'frequency_discount',
                label: 'Frequency',
                kind: PricingLineItemKind::FrequencyDiscount,
                adjustmentType: PricingAdjustmentType::Percentage,
                amount: new PriceAdjustment(
                    $range->minPence - $before->minPence,
                    $range->maxPence - $before->maxPence,
                ),
                detail: sprintf('%s (−%s%%)', $input->frequency->label(), rtrim(rtrim($percent, '0'), '.') ?: '0'),
            ),
        ];
    }

    private function frequencyDetail(?CleaningFrequency $frequency, $settings): string
    {
        return match ($frequency) {
            CleaningFrequency::Weekly => 'Includes the '.rtrim(rtrim((string) $settings->weekly_discount_percent, '0'), '.').'% weekly discount.',
            CleaningFrequency::Fortnightly => 'Includes the '.rtrim(rtrim((string) $settings->fortnightly_discount_percent, '0'), '.').'% fortnightly discount.',
            CleaningFrequency::Monthly => 'Monthly visits — no frequency discount.',
            default => 'One-off visit — no frequency discount.',
        };
    }

    public function calculateAddonContribution(Addon $addon, ?EstimateInput $context = null): PriceRange
    {
        $min = $addon->priceMinPence();
        $max = $addon->priceMaxPence();

        if ($addon->pricing_unit === AddonPricingUnit::PerBathroom && $context) {
            $multiplier = $context->bathroomMultiplier();
            $min *= $multiplier;
            $max *= $multiplier;
        }

        return new PriceRange($min, $max);
    }
}
