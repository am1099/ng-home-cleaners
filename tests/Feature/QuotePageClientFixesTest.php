<?php

namespace Tests\Feature;

use App\Enums\CleaningFrequency;
use App\Enums\PropertyType;
use App\Livewire\EstimateWizard;
use App\Models\PricingSetting;
use App\Models\PricingStartingPrice;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\Testimonial;
use App\Pricing\Data\EstimateInput;
use App\Pricing\Money;
use App\Pricing\PricingConfiguration;
use App\Pricing\PricingEngine;
use App\Pricing\RegularCleanGuidePrice;
use App\Services\QuoteRequestService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class QuotePageClientFixesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        PricingConfiguration::forget();
    }

    public function test_default_state_is_one_bed_one_bath_with_configured_starting_price(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $expected = app(PricingEngine::class)->calculate(new EstimateInput(
            service: $regular,
            propertyType: PropertyType::House,
            bedrooms: 1,
            bathrooms: 1,
            frequency: CleaningFrequency::OneOff,
        ));

        Livewire::test(EstimateWizard::class)
            ->assertSet('bedrooms', 1)
            ->assertSet('bathrooms', 1)
            ->assertSet('frequency', CleaningFrequency::OneOff->value)
            ->assertSee(Money::formatPence((int) $expected->finalSinglePricePence), false);

        $this->assertSame(6000, $expected->finalSinglePricePence);
        $this->assertSame('£60', app(RegularCleanGuidePrice::class)->formatted());
    }

    public function test_h1_and_seo_title_use_dynamic_starting_price(): void
    {
        $this->get(route('quote'))
            ->assertOk()
            ->assertSee('House cleaning in Nottingham from £60', false)
            ->assertSee('<title>House Cleaning Nottingham from £60 | NG Home Cleaners</title>', false)
            ->assertSee('Your price in seconds, then book on WhatsApp.', false);

        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        PricingStartingPrice::query()
            ->where('service_id', $regular->id)
            ->update(['min_pence' => 6500, 'max_pence' => 7500]);
        PricingSetting::instance()->update(['regular_min_pence' => 6500]);
        PricingConfiguration::forget();

        $this->get(route('quote'))
            ->assertOk()
            ->assertSee('House cleaning in Nottingham from £65', false)
            ->assertSee('<title>House Cleaning Nottingham from £65 | NG Home Cleaners</title>', false);
    }

    public function test_published_testimonials_appear_in_sort_order_without_demos(): void
    {
        Testimonial::query()->delete();

        Testimonial::query()->create([
            'customer_name' => 'Demo Skip',
            'rating' => 5,
            'review' => 'Should not appear on the quote page.',
            'location' => 'Nottingham',
            'is_published' => true,
            'published_at' => now(),
            'is_demo' => true,
            'sort_order' => 1,
        ]);

        Testimonial::query()->create([
            'customer_name' => 'Sharif',
            'rating' => 5,
            'review' => 'Brilliant regular clean every week.',
            'location' => 'West Bridgford',
            'is_published' => true,
            'published_at' => now(),
            'is_demo' => false,
            'sort_order' => 1,
        ]);

        Testimonial::query()->create([
            'customer_name' => 'Amira',
            'rating' => 5,
            'review' => 'Reliable and thorough team.',
            'location' => 'Beeston',
            'is_published' => true,
            'published_at' => now(),
            'is_demo' => false,
            'sort_order' => 2,
        ]);

        Testimonial::query()->create([
            'customer_name' => 'Hidden',
            'rating' => 4,
            'review' => 'Unpublished review.',
            'location' => 'Arnold',
            'is_published' => false,
            'published_at' => null,
            'is_demo' => false,
            'sort_order' => 0,
        ]);

        $html = $this->get(route('quote'))
            ->assertOk()
            ->assertSee('Sharif', false)
            ->assertSee('West Bridgford', false)
            ->assertSee('Brilliant regular clean every week.', false)
            ->assertSee('Amira', false)
            ->assertDontSee('Demo Skip', false)
            ->assertDontSee('Unpublished review.', false)
            ->getContent();

        $this->assertLessThan(
            strpos($html, 'Amira'),
            strpos($html, 'Sharif'),
            'Sharif should appear before Amira when sort_order is lower.',
        );
    }

    public function test_regular_clean_frequencies_recalculate_and_persist_on_whatsapp_lead(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $engine = app(PricingEngine::class);

        foreach ([
            CleaningFrequency::OneOff,
            CleaningFrequency::Weekly,
            CleaningFrequency::Fortnightly,
            CleaningFrequency::Monthly,
        ] as $frequency) {
            $expected = $engine->calculate(new EstimateInput(
                service: $regular,
                propertyType: PropertyType::House,
                bedrooms: 2,
                bathrooms: 1,
                frequency: $frequency,
            ));

            Livewire::test(EstimateWizard::class)
                ->set('serviceId', $regular->id)
                ->set('bedrooms', 2)
                ->set('bathrooms', 1)
                ->set('frequency', $frequency->value)
                ->assertSee(Money::formatPence((int) $expected->finalSinglePricePence), false)
                ->call('submitViaWhatsApp')
                ->assertHasNoErrors()
                ->assertDispatched('open-whatsapp');

            $lead = QuoteRequest::query()->latest('id')->firstOrFail();

            $this->assertSame($frequency->value, $lead->frequency);
            $this->assertSame($expected->finalSinglePricePence, $lead->guide_single_price_pence);
            $this->assertSame($expected->finalSinglePricePence, data_get($lead->pricing_snapshot, 'final_single_pence'));

            $message = app(QuoteRequestService::class)->whatsappMessage($lead);

            $this->assertStringContainsString("I'd like to BOOK: {$regular->name}", $message);
            $this->assertStringContainsString('2 bed, 1 bath', $message);
            $this->assertStringContainsString(Money::formatPence((int) $expected->finalSinglePricePence).' per visit', $message);

            if ($frequency !== CleaningFrequency::OneOff) {
                $this->assertStringContainsString(strtolower($frequency->label()), $message);
            } else {
                $this->assertStringNotContainsString('weekly', strtolower($message));
            }
        }
    }

    public function test_switching_away_from_regular_clears_frequency_discount(): void
    {
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $deep = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $component = Livewire::test(EstimateWizard::class)
            ->set('serviceId', $regular->id)
            ->set('frequency', CleaningFrequency::Weekly->value)
            ->assertSet('frequency', CleaningFrequency::Weekly->value)
            ->set('serviceId', $deep->id)
            ->assertSet('frequency', null);

        $this->assertNull($component->get('frequency'));
    }

    public function test_google_ads_conversion_labels_and_consent_mode_are_present(): void
    {
        $html = $this->get(route('quote'))
            ->assertOk()
            ->assertSee('AW-18369596770', false)
            ->assertSee('AW-18369596770/J-GNCIHs8LgdEOKap7dE', false)
            ->assertSee('AW-18369596770/GafGCITs8LgdEOKap7dE', false)
            ->assertSee('ad_storage', false)
            ->assertSee('ad_user_data', false)
            ->assertSee('ad_personalization', false)
            ->assertSee('analytics_storage', false)
            ->assertSee('Cookie policy', false)
            ->assertSee('Accept', false)
            ->assertSee('Reject', false)
            ->getContent();

        $this->assertStringContainsString("gtag('consent', 'default'", $html);
        $this->assertStringContainsString('wait_for_update', $html);
        $this->assertStringContainsString('data-google-ads-whatsapp-send-to', $html);
        $this->assertStringContainsString('data-google-ads-phone-send-to', $html);
    }
}
