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

    public function test_quote_wizard_has_sticky_guide_estimate(): void
    {
        $this->get('/get-a-quote')
            ->assertOk()
            ->assertSee('ng-sticky-estimate', false)
            ->assertSee('Your guide estimate', false);
    }
}
