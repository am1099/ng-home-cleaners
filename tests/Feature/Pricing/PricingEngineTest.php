<?php

namespace Tests\Feature\Pricing;

use App\Enums\CleaningFrequency;
use App\Enums\ConditionFlag;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Models\Addon;
use App\Models\PricingBedroomRule;
use App\Models\PricingExtraRoom;
use App\Models\PricingSetting;
use App\Models\PricingStartingPrice;
use App\Models\Service;
use App\Pricing\Data\EstimateInput;
use App\Pricing\PricingConfiguration;
use App\Pricing\PricingEngine;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingEngineTest extends TestCase
{
    use RefreshDatabase;

    private PricingEngine $engine;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        PricingConfiguration::forget();
        $this->engine = app(PricingEngine::class);
    }

    public function test_starting_prices_for_residential_services(): void
    {
        $cases = [
            ['regular-clean', PropertyType::Flat, 7500, 8500],
            ['regular-clean', PropertyType::House, 7500, 9000],
            ['deep-clean', PropertyType::Flat, 15500, 18000],
            ['deep-clean', PropertyType::House, 17000, 20000],
            ['end-of-tenancy', PropertyType::Flat, 20500, 23500],
            ['end-of-tenancy', PropertyType::House, 22500, 25500],
        ];

        foreach ($cases as [$slug, $type, $min, $max]) {
            $service = Service::query()->where('slug', $slug)->firstOrFail();
            $result = $this->engine->calculate(new EstimateInput(
                service: $service,
                propertyType: $type,
                bedrooms: 1,
            ));

            $this->assertSame($min, $result->baseSubtotal->minPence, "{$slug} {$type->value} min");
            $this->assertSame($max, $result->baseSubtotal->maxPence, "{$slug} {$type->value} max");
        }
    }

    public function test_included_bedroom_count_adds_no_extra(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $one = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 1,
        ));
        $studio = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 0,
        ));

        $this->assertSame($one->baseSubtotal->minPence, $studio->finalRange->minPence);
        $this->assertSame($one->baseSubtotal->maxPence, $studio->finalRange->maxPence);
        $this->assertFalse(collect($one->lineItems)->contains(fn ($item) => $item->key === 'extra_bedrooms'));
    }

    public function test_additional_bedrooms_apply_per_bedroom_rule(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $rule = PricingBedroomRule::query()->where('service_id', $service->id)->firstOrFail();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 3,
        ));

        $extraMin = 2 * $rule->extra_min_pence;
        $extraMax = 2 * $rule->extra_max_pence;

        $this->assertSame(7500 + $extraMin, $result->calculatedSubtotal->minPence);
        $this->assertSame(9000 + $extraMax, $result->calculatedSubtotal->maxPence);
        $this->assertTrue(collect($result->lineItems)->contains(fn ($item) => $item->key === 'extra_bedrooms'));
    }

    public function test_extra_rooms_apply_per_service_rates(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $bathroom = PricingExtraRoom::query()
            ->where('service_id', $service->id)
            ->where('room_type', 'bathroom')
            ->firstOrFail();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            bathrooms: 2,
            wcs: 1,
            kitchens: 2,
            receptionRooms: 2,
            floors: 3,
            extraRoomSlugs: ['office'],
        ));

        $expectedMin = 7500
            + $bathroom->min_pence
            + 600 // wc
            + 1200 // kitchen
            + 1200 // reception
            + 600 // floor
            + 600; // extra room

        $this->assertSame($expectedMin, $result->calculatedSubtotal->minPence);
    }

    public function test_conditions_add_fixed_ranges(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            conditionFlags: [ConditionFlag::Pets, ConditionFlag::Clutter],
        ));

        $this->assertSame(15500 + 1500 + 1500, $result->calculatedSubtotal->minPence);
        $this->assertSame(18000 + 2500 + 2500, $result->calculatedSubtotal->maxPence);
    }

    public function test_frequency_discounts_for_regular_clean(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $weekly = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            frequency: CleaningFrequency::Weekly,
        ));
        $fortnightly = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            frequency: CleaningFrequency::Fortnightly,
        ));
        $oneOff = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            frequency: CleaningFrequency::OneOff,
        ));

        $this->assertNotNull($weekly->finalSinglePricePence);
        $this->assertLessThan($oneOff->finalSinglePricePence, $weekly->finalSinglePricePence);
        $this->assertStringContainsString('From £', $weekly->displayHeadline);
        $this->assertTrue(collect($weekly->lineItems)->contains(fn ($item) => $item->key === 'frequency_discount'));
        $this->assertTrue(collect($fortnightly->lineItems)->contains(fn ($item) => $item->key === 'frequency_discount'));
        $this->assertFalse(collect($oneOff->lineItems)->contains(fn ($item) => $item->key === 'frequency_discount'));
    }

    public function test_furnishing_adjustments_for_deep_clean(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $empty = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 1,
            propertyStatus: PropertyStatus::Empty,
        ));
        $furnished = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 1,
            propertyStatus: PropertyStatus::Furnished,
        ));
        $part = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 1,
            propertyStatus: PropertyStatus::PartFurnished,
        ));

        $this->assertLessThan(17000, $empty->finalRange->minPence);
        $this->assertGreaterThan(17000, $furnished->finalRange->minPence);
        $this->assertSame(17000, $part->calculatedSubtotal->minPence);
    }

    public function test_rounding_to_five_pounds(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            bathrooms: 2,
        ));

        $this->assertSame(0, $result->finalRange->minPence % 500);
        $this->assertSame(0, $result->finalRange->maxPence % 500);
    }

    public function test_commercial_service_returns_manual_quote(): void
    {
        $service = Service::query()->where('slug', 'office-commercial')->firstOrFail();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
        ));

        $this->assertFalse($result->isNumericEstimate);
        $this->assertSame('Priced per visit', $result->displayHeadline);
    }

    public function test_snapshot_is_stable_after_admin_price_edit(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $before = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 2,
            frequency: CleaningFrequency::Fortnightly,
        ));

        $snapshot = $before->snapshot;

        PricingStartingPrice::query()
            ->where('service_id', $service->id)
            ->where('property_type', PropertyType::House)
            ->update(['min_pence' => 9900, 'max_pence' => 12000]);
        PricingConfiguration::forget();

        $this->assertSame($snapshot['final_range'], $before->snapshot['final_range']);
        $this->assertSame($snapshot['line_items'], $before->snapshot['line_items']);
    }

    public function test_admin_starting_price_change_affects_new_estimates(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        PricingStartingPrice::query()
            ->where('service_id', $service->id)
            ->where('property_type', PropertyType::House)
            ->update(['min_pence' => 8000, 'max_pence' => 10000]);
        PricingConfiguration::forget();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 1,
        ));

        $this->assertSame(8000, $result->baseSubtotal->minPence);
        $this->assertSame(10000, $result->baseSubtotal->maxPence);
    }

    public function test_mixed_quote_includes_readable_line_items(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();
        $addon = Addon::query()->where('slug', 'single-oven')->firstOrFail();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 3,
            bathrooms: 2,
            propertyStatus: PropertyStatus::Furnished,
            conditionFlags: [ConditionFlag::HeavyLimescale],
            addonIds: [$addon->id],
        ));

        $keys = collect($result->lineItems)->pluck('key');

        $this->assertTrue($keys->contains('starting_price'));
        $this->assertTrue($keys->contains('extra_bedrooms'));
        $this->assertTrue($keys->contains('extra_room_bathroom'));
        $this->assertTrue($keys->contains('condition_heavy_limescale'));
        $this->assertTrue($keys->contains('addon_single-oven'));
        $this->assertTrue($keys->contains('furnishing'));
        $this->assertTrue($result->isNumericEstimate);
    }

    public function test_regular_minimum_floor_is_enforced(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        PricingSetting::query()->first()?->update([
            'regular_min_pence' => 12000,
        ]);
        PricingConfiguration::forget();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            frequency: CleaningFrequency::Weekly,
        ));

        $this->assertSame(12000, $result->finalSinglePricePence);
        $this->assertStringContainsString('From £120', $result->displayHeadline);
    }

    public function test_bungalow_starting_price_matches_house(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $house = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 1,
        ));
        $bungalow = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Bungalow,
            bedrooms: 1,
        ));

        $this->assertSame($house->baseSubtotal->minPence, $bungalow->baseSubtotal->minPence);
        $this->assertSame($house->baseSubtotal->maxPence, $bungalow->baseSubtotal->maxPence);
    }

    public function test_percentage_line_items_are_furnishing_or_frequency_only(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $result = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::House,
            bedrooms: 2,
            propertyStatus: PropertyStatus::Furnished,
            conditionFlags: [ConditionFlag::Pets],
        ));

        $kinds = collect($result->lineItems)->pluck('kind')->map(fn ($kind) => $kind->value);

        $this->assertFalse($kinds->contains('percentage_adjustment'));
        $this->assertFalse($kinds->contains('fixed_adjustment'));
        $this->assertTrue($kinds->contains('furnishing_adjustment'));
    }

    public function test_every_condition_flag_increases_calculated_subtotal(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $base = $this->engine->calculate(new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
        ));

        foreach (ConditionFlag::cases() as $flag) {
            $withFlag = $this->engine->calculate(new EstimateInput(
                service: $service,
                propertyType: PropertyType::Flat,
                bedrooms: 1,
                conditionFlags: [$flag],
            ));

            $this->assertGreaterThan(
                $base->calculatedSubtotal->minPence,
                $withFlag->calculatedSubtotal->minPence,
                "Condition {$flag->value} should increase min",
            );
        }
    }
}
