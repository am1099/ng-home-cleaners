<?php

namespace Tests\Feature;

use App\Models\SiteSetting;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_homepage_returns_successful_response(): void
    {
        $settings = SiteSetting::instance();

        $this->get('/')
            ->assertOk()
            ->assertSee('Get a free estimate', false)
            ->assertSee('DBS-checked', false)
            ->assertSee($settings->home_hero_title, false);
    }
}
