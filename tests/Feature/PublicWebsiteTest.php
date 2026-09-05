<?php

namespace Tests\Feature;

use App\Livewire\EstimateWizard;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\SiteSetting;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicWebsiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_all_public_routes_return_successful_response(): void
    {
        $routes = [
            '/',
            '/services',
            '/areas',
            '/about',
            '/contact',
            '/get-a-quote',
            '/privacy',
            '/terms',
            '/cookies',
        ];

        foreach ($routes as $route) {
            $this->get($route)->assertOk();
        }
    }

    public function test_service_and_area_show_pages_resolve_by_slug(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $area = ServiceArea::query()->where('slug', 'city-centre')->firstOrFail();

        $this->get(route('services.show', $service))->assertOk()->assertSee($service->name, false);
        $this->get(route('areas.show', $area))->assertOk()->assertSee($area->name, false);
    }

    public function test_inactive_service_returns_not_found(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $service->update(['is_active' => false]);

        $this->get(route('services.show', $service->slug))->assertNotFound();
    }

    public function test_inactive_area_returns_not_found(): void
    {
        $area = ServiceArea::query()->where('slug', 'city-centre')->firstOrFail();
        $area->update(['is_active' => false]);

        $this->get(route('areas.show', $area->slug))->assertNotFound();
    }

    public function test_homepage_shows_cms_services_and_hero_content(): void
    {
        $settings = SiteSetting::instance();

        $this->get('/')
            ->assertOk()
            ->assertSee($settings->home_hero_title, false)
            ->assertSee('Regular clean', false)
            ->assertSee('Deep clean', false);
    }

    public function test_service_page_shows_inclusions_and_exclusions(): void
    {
        $service = Service::query()->where('slug', 'deep-clean')->firstOrFail();

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('What is included', false)
            ->assertSee('What is not included', false)
            ->assertSee('<details', false)
            ->assertSee('01', false)
            ->assertSee('ng-exclusion-list', false)
            ->assertSee('break-inside-avoid', false)
            ->assertSee('md:columns-2', false)
            ->assertDontSee('md:hidden', false);
    }

    public function test_inner_pages_use_faint_text_hero_band(): void
    {
        foreach (['/services', '/about', '/contact', '/areas'] as $route) {
            $this->get($route)
                ->assertOk()
                ->assertSee('from-brand-50', false)
                ->assertSee('via-surface-page', false);
        }
    }

    public function test_contact_page_shows_settings_contact_details(): void
    {
        $settings = SiteSetting::instance();

        $this->get('/contact')
            ->assertOk()
            ->assertSee($settings->phoneDisplay(), false)
            ->assertSee($settings->email, false);
    }

    public function test_areas_page_lists_all_districts(): void
    {
        $this->get('/areas')
            ->assertOk()
            ->assertSee('District by district', false)
            ->assertSee('NG6', false)
            ->assertSee('Bulwell', false)
            ->assertSee('NG16', false)
            ->assertSee('Just outside the line?', false);
    }

    public function test_quote_page_is_livewire_estimator(): void
    {
        $this->get('/get-a-quote')
            ->assertOk()
            ->assertSee('instant estimate', false)
            ->assertSeeLivewire(EstimateWizard::class);
    }
}
