<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLayoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_homepage_includes_accessible_public_layout(): void
    {
        $settings = SiteSetting::instance();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Skip to main content', false);
        $response->assertSee('id="main-content"', false);
        $response->assertSee('data-mobile-nav-toggle', false);
        $response->assertSee('aria-controls="mobile-navigation"', false);
        $response->assertSee('ng-mobile-nav', false);
        $response->assertSee('ng-mobile-nav__drawer', false);
        $response->assertSee('data-open="false"', false);
        $response->assertSee('Get a free estimate', false);
        $response->assertSee($settings->phoneDisplay(), false);
        $response->assertSee($settings->email, false);
    }

    public function test_homepage_does_not_include_guides_section(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Guides', false)
            ->assertDontSee('guide-card', false);
    }

    public function test_public_pages_use_shared_layout(): void
    {
        $routes = ['/', '/services', '/areas', '/about', '/contact', '/get-a-quote', '/privacy', '/terms', '/cookies'];

        foreach ($routes as $route) {
            $this->get($route)
                ->assertOk()
                ->assertSee('data-mobile-nav', false)
                ->assertSee('&copy; '.now()->year, false);
        }
    }

    public function test_google_ads_tag_is_on_public_pages_and_not_admin(): void
    {
        $snippet = 'https://www.googletagmanager.com/gtag/js?id=AW-18369596770';

        $this->get('/')->assertOk()->assertSee($snippet, false);
        $this->get('/get-a-quote')->assertOk()->assertSee($snippet, false);
        $this->get('/confirm')->assertOk()->assertSee($snippet, false);
        $this->get('/cookies')->assertOk()->assertSee('Google Ads', false);
        $this->get('/admin/login')->assertOk()->assertDontSee('googletagmanager.com', false);
    }

    public function test_quote_wizard_has_sticky_guide_estimate(): void
    {
        $html = $this->get('/get-a-quote')
            ->assertOk()
            ->assertSee('Your price', false)
            ->assertSee('ng-sticky-estimate-desktop', false)
            ->assertSee('window.livewireScriptConfig', false)
            ->getContent();

        $livewire = strpos($html, 'window.livewireScriptConfig');
        $build = strpos($html, '/build/assets/');

        if ($build !== false) {
            $this->assertLessThan(
                $build,
                $livewire,
                'Livewire script config must load before the Vite bundle.',
            );
        }
    }
}
