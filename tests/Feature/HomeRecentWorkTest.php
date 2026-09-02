<?php

namespace Tests\Feature;

use App\Models\RecentWork;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeRecentWorkTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_recent_work_section_hidden_when_empty(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Before &amp; after.', false);
    }

    public function test_recent_work_section_visible_when_published_and_enabled(): void
    {
        RecentWork::factory()->create([
            'title' => 'Bathroom floor',
            'description' => 'GROUND-IN GRIME LIFTED',
            'is_published' => true,
            'published_at' => now(),
        ]);

        SiteSetting::instance()->update(['show_recent_work' => true]);
        app(SiteSettingsService::class)->forget();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Bathroom floor', false)
            ->assertSee('GROUND-IN GRIME LIFTED', false);
    }

    public function test_recent_work_section_hidden_when_toggle_off(): void
    {
        RecentWork::factory()->create([
            'title' => 'Bathroom floor',
            'is_published' => true,
            'published_at' => now(),
        ]);

        SiteSetting::instance()->update(['show_recent_work' => false]);
        app(SiteSettingsService::class)->forget();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Bathroom floor', false);
    }

    public function test_homepage_shows_coverage_panel(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Coverage', false)
            ->assertSee('Areas we cover', false)
            ->assertSee('Do you cover my postcode?', false);
    }
}
