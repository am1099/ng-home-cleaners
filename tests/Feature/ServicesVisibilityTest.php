<?php

namespace Tests\Feature;

use App\Livewire\EstimateWizard;
use App\Models\Service;
use App\Services\SiteSettingsService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ServicesVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_active_service_is_visible_on_public_site_and_estimator(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee($service->name, false);

        $this->get('/')
            ->assertOk()
            ->assertSee($service->name, false);

        Livewire::test(EstimateWizard::class)
            ->assertSee($service->name, false);
    }

    public function test_inactive_service_is_hidden_everywhere_public(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();
        $service->update(['is_active' => false]);

        $this->get(route('services.show', $service))->assertNotFound();

        $this->get('/')
            ->assertOk()
            ->assertDontSee($service->name, false);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee(route('services.show', $service, absolute: true), false);

        Livewire::test(EstimateWizard::class)
            ->assertDontSee($service->name, false);
    }

    public function test_cms_name_change_is_reflected_on_public_pages(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $service->update([
            'name' => 'Weekly home tidy',
            'card_title' => 'Weekly home tidy',
            'estimate_description' => 'CMS-updated estimate blurb for regular cleans.',
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Weekly home tidy', false);

        Livewire::test(EstimateWizard::class)
            ->set('serviceId', $service->id)
            ->assertSee('CMS-updated estimate blurb for regular cleans.', false);
    }

    public function test_quote_query_string_preselects_service(): void
    {
        $deep = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        Livewire::withQueryParams(['service' => 'deep-clean'])
            ->test(EstimateWizard::class)
            ->assertSet('serviceId', $deep->id);
    }

    public function test_seeded_recent_work_appears_when_enabled(): void
    {
        app(SiteSettingsService::class)->forget();

        $this->get('/')
            ->assertOk()
            ->assertSee('Bathroom floor', false)
            ->assertSee('Limescale removal', false);
    }
}
