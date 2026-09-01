<?php

namespace App\Data;

use App\Enums\ArrivalWindow;
use App\Enums\CleaningFrequency;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Livewire\EstimateWizard;
use App\Models\Service;
use App\Pricing\Data\EstimateInput;
use App\Pricing\EstimateInputFactory;
use App\Support\UkContactNormalizer;

final readonly class WizardSubmissionData
{
    /**
     * @param  list<string>  $extraRooms
     * @param  list<string>  $conditionFlags
     * @param  list<int>  $addonIds
     */
    public function __construct(
        public int $serviceId,
        public ?string $frequency,
        public string $propertyType,
        public int $bedrooms,
        public bool $splitLevelFlat,
        public int $floors,
        public int $bathrooms,
        public int $wcs,
        public int $kitchens,
        public int $receptionRooms,
        public array $extraRooms,
        public ?string $propertyStatus,
        public array $conditionFlags,
        public string $conditionNotes,
        public array $addonIds,
        public string $preferredDate,
        public string $arrivalWindow,
        public string $firstName,
        public string $lastName,
        public string $phone,
        public string $email,
        public string $postcode,
        public string $addressLine1,
        public ?string $addressLine2,
        public string $city,
        public string $parkingNotes,
        public string $accessNotes,
    ) {}

    public static function fromWizard(EstimateWizard $wizard): self
    {
        return new self(
            serviceId: (int) $wizard->serviceId,
            frequency: $wizard->frequency,
            propertyType: (string) $wizard->propertyType,
            bedrooms: $wizard->bedrooms,
            splitLevelFlat: $wizard->splitLevelFlat,
            floors: $wizard->floors,
            bathrooms: $wizard->bathrooms,
            wcs: $wizard->wcs,
            kitchens: $wizard->kitchens,
            receptionRooms: $wizard->receptionRooms,
            extraRooms: $wizard->extraRooms,
            propertyStatus: $wizard->propertyStatus,
            conditionFlags: $wizard->conditionFlags,
            conditionNotes: $wizard->conditionNotes,
            addonIds: $wizard->addonIds,
            preferredDate: (string) $wizard->preferredDate,
            arrivalWindow: (string) $wizard->arrivalWindow,
            firstName: trim($wizard->firstName),
            lastName: trim($wizard->lastName),
            phone: UkContactNormalizer::formatPhoneDisplay($wizard->phone),
            email: UkContactNormalizer::normalizeEmail($wizard->email),
            postcode: UkContactNormalizer::normalizePostcode($wizard->postcode),
            addressLine1: trim($wizard->addressLine1),
            addressLine2: filled($wizard->addressLine2) ? trim($wizard->addressLine2) : null,
            city: trim($wizard->city),
            parkingNotes: $wizard->resolvedParkingNotes(),
            accessNotes: $wizard->resolvedAccessNotes(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function selectionsSnapshot(): array
    {
        return [
            'service_id' => $this->serviceId,
            'frequency' => $this->frequency,
            'property_type' => $this->propertyType,
            'bedrooms' => $this->bedrooms,
            'split_level_flat' => $this->splitLevelFlat,
            'floors' => $this->floors,
            'bathrooms' => $this->bathrooms,
            'wcs' => $this->wcs,
            'kitchens' => $this->kitchens,
            'reception_rooms' => $this->receptionRooms,
            'extra_rooms' => $this->extraRooms,
            'property_status' => $this->propertyStatus,
            'condition_flags' => $this->conditionFlags,
            'condition_notes' => $this->conditionNotes,
            'addon_ids' => $this->addonIds,
            'preferred_date' => $this->preferredDate,
            'arrival_window' => $this->arrivalWindow,
            'contact' => [
                'first_name' => $this->firstName,
                'last_name' => $this->lastName,
                'phone' => $this->phone,
                'email' => $this->email,
                'postcode' => $this->postcode,
                'address_line1' => $this->addressLine1,
                'address_line2' => $this->addressLine2,
                'city' => $this->city,
                'parking_notes' => $this->parkingNotes,
                'access_notes' => $this->accessNotes,
            ],
        ];
    }

    public function toEstimateInput(): EstimateInput
    {
        $service = Service::query()->findOrFail($this->serviceId);
        $propertyType = PropertyType::from($this->propertyType);

        return EstimateInputFactory::make(
            service: $service,
            propertyType: $propertyType,
            bedrooms: $this->bedrooms,
            bathrooms: $this->bathrooms,
            wcs: $this->wcs,
            kitchens: $this->kitchens,
            receptionRooms: $this->receptionRooms,
            floors: $this->floors,
            extraRoomSlugs: $this->extraRooms,
            frequency: $this->frequency ? CleaningFrequency::from($this->frequency) : null,
            propertyStatus: $this->propertyStatus ? PropertyStatus::from($this->propertyStatus) : null,
            conditionFlagValues: $this->conditionFlags,
            addonIds: $this->addonIds,
            postcode: $this->postcode,
            preferredDate: $this->preferredDate,
            conditionNotes: $this->conditionNotes,
            parkingNotes: $this->parkingNotes,
            accessNotes: $this->accessNotes,
            arrivalWindow: ArrivalWindow::from($this->arrivalWindow),
        );
    }
}
