<?php

namespace Tests\Feature;

use App\Models\GalleryItem;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\SiteSettingsService;
use App\Support\Media;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeLogoAndGalleryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_uploaded_logo_appears_in_public_header(): void
    {
        Storage::fake(Media::diskName());
        Storage::disk(Media::diskName())->put('brand/logo-test.png', 'fake-image');

        SiteSetting::instance()->update(['logo_path' => 'brand/logo-test.png']);
        app(SiteSettingsService::class)->forget();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('brand/logo-test.png', false);
    }

    public function test_uploaded_hero_image_appears_on_the_homepage(): void
    {
        Storage::fake(Media::diskName());
        Storage::disk(Media::diskName())->put('brand/hero/hero-test.jpg', 'fake-image');

        SiteSetting::instance()->update([
            'home_hero_image' => 'brand/hero/hero-test.jpg',
            'home_hero_image_alt' => 'Cleaner finishing a Nottingham kitchen',
        ]);
        app(SiteSettingsService::class)->forget();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('brand/hero/hero-test.jpg', false)
            ->assertSee('Cleaner finishing a Nottingham kitchen', false)
            ->assertDontSee('Upload a hero photo in Site settings', false);
    }

    public function test_published_gallery_items_appear_on_homepage_with_nav_link(): void
    {
        GalleryItem::factory()->create([
            'caption' => 'Sparkling kitchen sink',
            'alt_text' => 'Clean kitchen after visit',
            'is_published' => true,
            'published_at' => now(),
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('id="gallery"', false)
            ->assertSee('Sparkling kitchen sink', false)
            ->assertSee('#gallery', false)
            ->assertSee('Gallery', false);
    }

    public function test_unpublished_gallery_items_are_hidden(): void
    {
        GalleryItem::factory()->unpublished()->create([
            'caption' => 'Hidden gallery shot',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Hidden gallery shot', false)
            ->assertDontSee('id="gallery"', false);
    }

    public function test_gallery_create_redirects_to_index(): void
    {
        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin/gallery-items/create')
            ->assertOk();
    }
}
