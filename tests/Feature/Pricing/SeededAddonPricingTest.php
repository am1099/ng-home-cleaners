<?php

namespace Tests\Feature\Pricing;

use App\Enums\PropertyType;
use App\Models\Addon;
use App\Models\Service;
use App\Pricing\AddonPriceFormatter;
use App\Pricing\Data\EstimateInput;
use App\Pricing\PricingEngine;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeededAddonPricingTest extends TestCase
{
    use RefreshDatabase;

    private PricingEngine $engine;

    private AddonPriceFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->engine = app(PricingEngine::class);
        $this->formatter = app(AddonPriceFormatter::class);
    }

    public function test_every_seeded_addon_display_matches_engine_contribution(): void
    {
        $context = new EstimateInput(
            service: Service::query()->where('slug', 'deep-clean')->firstOrFail(),
            propertyType: PropertyType::Flat,
            bedrooms: 2,
            bathrooms: 2,
            wcs: 1,
        );

        Addon::query()->orderBy('sort_order')->get()->each(function (Addon $addon) use ($context): void {
            $contribution = $this->engine->calculateAddonContribution($addon, $context);
            $label = $this->formatter->displayLabel($addon, $context);

            $this->assertSame($addon->priceMinPence(), $addon->price_pence);
            $this->assertSame($addon->priceMaxPence(), $addon->price_max_pence);

            $this->assertStringContainsString(
                '£'.number_format($contribution->minPence / 100, 0),
                $label,
            );
            $this->assertStringContainsString(
                '£'.number_format($contribution->maxPence / 100, 0),
                $label,
            );

            if ($addon->show_from_prefix) {
                $this->assertStringContainsString('from ', $label);
            }
        });
    }

    public function test_each_seeded_addon_adjustment_is_applied_in_full_estimate(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        Addon::query()->orderBy('sort_order')->get()->each(function (Addon $addon) use ($service): void {
            $context = new EstimateInput(
                service: $service,
                propertyType: PropertyType::Flat,
                bedrooms: 1,
                bathrooms: 2,
                wcs: 1,
            );

            $withAddon = $this->engine->calculate(new EstimateInput(
                service: $service,
                propertyType: PropertyType::Flat,
                bedrooms: 1,
                bathrooms: 2,
                wcs: 1,
                addonIds: [$addon->id],
            ));

            $contribution = $this->engine->calculateAddonContribution($addon, $context);

            $lineItem = collect($withAddon->extras)->first(
                fn ($item) => $item->key === 'addon_'.$addon->slug,
            );

            $this->assertNotNull($lineItem, "Missing line item for addon [{$addon->slug}]");
            $this->assertSame($contribution->minPence, $lineItem->amount->minPence);
            $this->assertSame($contribution->maxPence, $lineItem->amount->maxPence);
        });
    }

    public function test_limescale_addon_multiplies_by_bathroom_count_including_wcs(): void
    {
        $addon = Addon::query()->where('slug', 'limescale-treatment')->firstOrFail();
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $singleBathroom = $this->engine->calculateAddonContribution($addon, new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            bathrooms: 1,
            wcs: 0,
        ));

        $threeWetRooms = $this->engine->calculateAddonContribution($addon, new EstimateInput(
            service: $service,
            propertyType: PropertyType::Flat,
            bedrooms: 1,
            bathrooms: 2,
            wcs: 1,
        ));

        $this->assertSame($addon->priceMinPence(), $singleBathroom->minPence);
        $this->assertSame($addon->priceMinPence() * 3, $threeWetRooms->minPence);
        $this->assertSame($addon->priceMaxPence() * 3, $threeWetRooms->maxPence);
    }

    public function test_addon_formatted_price_on_model_uses_engine_formatter(): void
    {
        $addon = Addon::query()->where('slug', 'single-oven')->firstOrFail();

        $this->assertSame(
            $this->formatter->displayLabel($addon),
            $addon->formattedPrice(),
        );
    }
}
