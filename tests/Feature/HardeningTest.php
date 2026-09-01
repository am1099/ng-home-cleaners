<?php

namespace Tests\Feature;

use App\Livewire\EstimateWizard;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use App\Support\Analytics\Analytics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Livewire;
use Tests\TestCase;

class HardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_site_settings_service_caches_attributes_not_model_instances(): void
    {
        $settings = SiteSetting::instance();
        Cache::flush();

        $service = app(SiteSettingsService::class);
        $first = $service->get();
        $second = $service->get();

        $this->assertTrue(Cache::has(SiteSettingsService::CACHE_KEY));
        $this->assertIsArray(Cache::get(SiteSettingsService::CACHE_KEY));
        $this->assertSame($settings->id, $first->id);
        $this->assertSame($first->business_name, $second->business_name);
        $this->assertSame(spl_object_id($first), spl_object_id($second));
    }

    public function test_site_settings_service_recovers_from_corrupt_cache_payload(): void
    {
        SiteSetting::instance();
        Cache::forever(SiteSettingsService::CACHE_KEY, 'not-an-attributes-array');

        $settings = app(SiteSettingsService::class)->get();

        $this->assertInstanceOf(SiteSetting::class, $settings);
        $this->assertIsArray(Cache::get(SiteSettingsService::CACHE_KEY));
    }

    public function test_error_pages_render_without_debug_exposure(): void
    {
        config(['app.debug' => false]);

        $this->get('/this-page-definitely-does-not-exist-xyz')
            ->assertNotFound()
            ->assertDontSee('Stack trace', false)
            ->assertDontSee('Illuminate\\', false);

        $this->view('errors.419')->assertSee('session has expired', false);
        $this->view('errors.500')->assertSee('Something went wrong', false);
        $this->view('errors.500')->assertDontSee('Stack trace', false);
    }

    public function test_quote_submit_is_rate_limited(): void
    {
        Service::factory()->create(['is_active' => true, 'slug' => 'regular-clean']);

        $key = 'quote-submit:'.request()->ip();
        RateLimiter::clear($key);

        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key, 60);
        }

        Livewire::test(EstimateWizard::class)
            ->call('submit')
            ->assertHasErrors(['submit']);
    }

    public function test_analytics_event_names_are_stable(): void
    {
        $this->assertSame('quote_started', Analytics::QUOTE_STARTED);
        $this->assertSame('quote_step_completed', Analytics::QUOTE_STEP_COMPLETED);
        $this->assertSame('quote_completed', Analytics::QUOTE_COMPLETED);
        $this->assertSame('quote_photos_added', Analytics::QUOTE_PHOTOS_ADDED);
        $this->assertSame('quote_whatsapp_clicked', Analytics::QUOTE_WHATSAPP_CLICKED);
        $this->assertSame('whatsapp_quote', Analytics::WHATSAPP_QUOTE);
        $this->assertSame('whatsapp_clicked', Analytics::WHATSAPP_CLICKED);
        $this->assertSame('phone_clicked', Analytics::PHONE_CLICKED);
        $this->assertSame('service_viewed', Analytics::SERVICE_VIEWED);
        $this->assertFalse(config('analytics.enabled'));
    }

    public function test_service_page_includes_analytics_hook_payload(): void
    {
        $service = Service::factory()->create([
            'is_active' => true,
            'slug' => 'end-of-tenancy',
            'name' => 'End of tenancy',
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('service_viewed', false)
            ->assertSee('end-of-tenancy', false);
    }
}
