<?php

namespace App\Pricing\Data;

use App\Enums\CleaningFrequency;
use App\Enums\ConditionFlag;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Service;

final readonly class EstimateInput
{
    /**
     * @param  list<ConditionFlag>  $conditionFlags
     * @param  list<string>  $extraRoomSlugs
     * @param  list<int>  $addonIds
     */
    public function __construct(
        public Service $service,
        public PropertyType $propertyType,
        public int $bedrooms,
        public int $bathrooms = 1,
        public int $wcs = 0,
        public int $kitchens = 1,
        public int $receptionRooms = 1,
        public int $floors = 2,
        public array $extraRoomSlugs = [],
        public ?CleaningFrequency $frequency = CleaningFrequency::Fortnightly,
        public ?PropertyStatus $propertyStatus = null,
        public array $conditionFlags = [],
        public array $addonIds = [],
        public ?string $postcode = null,
        public ?string $preferredDate = null,
        public bool $nonDefaultParkingOrAccess = false,
        public ?string $detailsText = null,
        public ?int $narrowingSignalOverride = null,
    ) {}

    public function bathroomMultiplier(): int
    {
        return max(1, $this->bathrooms + $this->wcs);
    }

    public function narrowingSignalCount(): int
    {
        if ($this->narrowingSignalOverride !== null) {
            return max(0, $this->narrowingSignalOverride);
        }

        $signals = 0;

        if ($this->conditionFlags !== [] || $this->extraRoomSlugs !== []) {
            $signals++;
        }

        if (strlen(trim($this->postcode ?? '')) >= 3) {
            $signals++;
        }

        if (filled($this->preferredDate)) {
            $signals++;
        }

        if ($this->nonDefaultParkingOrAccess) {
            $signals++;
        }

        if (strlen(trim($this->detailsText ?? '')) > 12) {
            $signals++;
        }

        return $signals;
    }
}
