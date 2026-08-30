<?php

namespace App\Pricing;

use App\Enums\ConditionFlag;
use App\Enums\PropertyType;
use App\Enums\RoomModifierType;
use App\Models\PricingBedroomRule;
use App\Models\PricingCondition;
use App\Models\PricingExtraRoom;
use App\Models\PricingSetting;
use App\Models\PricingStartingPrice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class PricingConfiguration
{
    public const CACHE_KEY = 'pricing.configuration.v2';

    /**
     * @param  Collection<string, PricingStartingPrice>  $startingPrices
     * @param  Collection<int, PricingBedroomRule>  $bedroomRules
     * @param  Collection<string, PricingExtraRoom>  $extraRooms
     * @param  Collection<string, PricingCondition>  $conditions
     */
    public function __construct(
        public PricingSetting $settings,
        public Collection $startingPrices,
        public Collection $bedroomRules,
        public Collection $extraRooms,
        public Collection $conditions,
    ) {}

    public static function load(): self
    {
        $payload = Cache::rememberForever(self::CACHE_KEY, function (): array {
            return [
                'settings' => PricingSetting::instance()->getAttributes(),
                'starting_prices' => PricingStartingPrice::query()->get()->map->getAttributes()->all(),
                'bedroom_rules' => PricingBedroomRule::query()->get()->map->getAttributes()->all(),
                'extra_rooms' => PricingExtraRoom::query()->orderBy('sort_order')->get()->map->getAttributes()->all(),
                'conditions' => PricingCondition::query()->get()->map->getAttributes()->all(),
            ];
        });

        return self::fromPayload($payload);
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget('pricing.configuration.v1');
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromPayload(array $payload): self
    {
        $settings = (new PricingSetting)->newFromBuilder($payload['settings']);
        $settings->exists = true;

        $starting = collect($payload['starting_prices'])->mapWithKeys(function (array $attributes): array {
            $row = (new PricingStartingPrice)->newFromBuilder($attributes);
            $row->exists = true;
            $type = $row->property_type instanceof PropertyType
                ? $row->property_type->value
                : (string) $row->property_type;

            return [self::startingKey((int) $row->service_id, $type) => $row];
        });

        $bedrooms = collect($payload['bedroom_rules'])->mapWithKeys(function (array $attributes): array {
            $row = (new PricingBedroomRule)->newFromBuilder($attributes);
            $row->exists = true;

            return [(int) $row->service_id => $row];
        });

        $rooms = collect($payload['extra_rooms'])->mapWithKeys(function (array $attributes): array {
            $row = (new PricingExtraRoom)->newFromBuilder($attributes);
            $row->exists = true;
            $type = $row->room_type instanceof RoomModifierType
                ? $row->room_type->value
                : (string) $row->room_type;

            return [self::roomKey((int) $row->service_id, $type) => $row];
        });

        $conditions = collect($payload['conditions'])->mapWithKeys(function (array $attributes): array {
            $row = (new PricingCondition)->newFromBuilder($attributes);
            $row->exists = true;
            $flag = $row->condition_flag instanceof ConditionFlag
                ? $row->condition_flag->value
                : (string) $row->condition_flag;

            return [self::conditionKey((int) $row->service_id, $flag) => $row];
        });

        return new self($settings, $starting, $bedrooms, $rooms, $conditions);
    }

    public function startingPrice(int $serviceId, PropertyType $propertyType): ?PricingStartingPrice
    {
        return $this->startingPrices->get(self::startingKey($serviceId, $propertyType->value));
    }

    public function bedroomRule(int $serviceId): ?PricingBedroomRule
    {
        return $this->bedroomRules->get($serviceId);
    }

    public function extraRoom(int $serviceId, RoomModifierType $roomType): ?PricingExtraRoom
    {
        return $this->extraRooms->get(self::roomKey($serviceId, $roomType->value));
    }

    public function condition(int $serviceId, ConditionFlag $flag): ?PricingCondition
    {
        return $this->conditions->get(self::conditionKey($serviceId, $flag->value));
    }

    public static function startingKey(int $serviceId, string $propertyType): string
    {
        return $serviceId.'|'.$propertyType;
    }

    public static function roomKey(int $serviceId, string $roomType): string
    {
        return $serviceId.'|'.$roomType;
    }

    public static function conditionKey(int $serviceId, string $flag): string
    {
        return $serviceId.'|'.$flag;
    }
}
