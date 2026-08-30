<?php

namespace App\Pricing;

use App\Enums\ArrivalWindow;
use App\Enums\CleaningFrequency;
use App\Enums\ConditionFlag;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Service;
use App\Pricing\Data\EstimateInput;
use App\Support\UkContactNormalizer;

final class EstimateInputFactory
{
    /**
     * @param  list<string>  $conditionFlagValues
     * @param  list<string>  $extraRoomSlugs
     * @param  list<int>  $addonIds
     */
    public static function make(
        Service $service,
        PropertyType $propertyType,
        int $bedrooms,
        int $bathrooms,
        int $wcs,
        int $kitchens,
        int $receptionRooms,
        int $floors,
        array $extraRoomSlugs,
        ?CleaningFrequency $frequency,
        ?PropertyStatus $propertyStatus,
        array $conditionFlagValues,
        array $addonIds,
        ?string $postcode,
        ?string $preferredDate,
        ?string $conditionNotes,
        ?string $parkingNotes,
        ?string $accessNotes,
        ?ArrivalWindow $arrivalWindow,
    ): EstimateInput {
        $flags = array_values(array_filter(array_map(
            fn (string $value) => ConditionFlag::tryFrom($value),
            $conditionFlagValues,
        )));

        $detailsText = trim(collect([$conditionNotes, $accessNotes, $parkingNotes])
            ->filter()
            ->implode(' '));

        $nonDefaultParkingOrAccess = filled($parkingNotes)
            || filled($accessNotes)
            || ($arrivalWindow !== null && $arrivalWindow !== ArrivalWindow::Flexible);

        return new EstimateInput(
            service: $service,
            propertyType: $propertyType->pricingPropertyType(),
            bedrooms: min(max($bedrooms, 0), 5),
            bathrooms: $bathrooms,
            wcs: $wcs,
            kitchens: $kitchens,
            receptionRooms: $receptionRooms,
            floors: $floors,
            extraRoomSlugs: $extraRoomSlugs,
            frequency: $service->isRegularClean() ? ($frequency ?? CleaningFrequency::Fortnightly) : null,
            propertyStatus: $service->appliesPropertyStatusMultipliers() ? $propertyStatus : null,
            conditionFlags: $flags,
            addonIds: $addonIds,
            postcode: UkContactNormalizer::normalizePostcode($postcode ?? ''),
            preferredDate: $preferredDate,
            nonDefaultParkingOrAccess: $nonDefaultParkingOrAccess,
            detailsText: $detailsText !== '' ? $detailsText : null,
        );
    }
}
