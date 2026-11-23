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

    public function test_footer_logo_image_uses_white_treatment_on_dark_footer(): void
    {
        Storage::fake(Media::diskName());
        Storage::disk(Media::diskName())->put('brand/logo-test.png', 'fake-image');

        SiteSetting::instance()->update(['logo_path' => 'brand/logo-test.png']);
        app(SiteSettingsService::class)->forget();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('brightness-0 invert', false);
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
            ->assertSee('ng-gallery-grid', false)
            ->assertSee('lg:grid-cols-4', false)
            ->assertDontSee('ng-gallery-carousel', false)
            ->assertDontSee('Swipe through recent jobs', false)
            ->assertDontSee('from-black/80', false)
            ->assertSee('data-lightbox-group="home-gallery"', false)
            ->assertSee('data-lightbox-caption="Sparkling kitchen sink"', false)
            ->assertSee('alt="Clean kitchen after visit"', false)
            ->assertSee('#gallery', false)
            ->assertSee('Gallery', false)
            ->assertSee('items-start', false);
    }

    public function test_gallery_alt_text_is_optional_and_not_shown_on_the_photo(): void
    {
        GalleryItem::factory()->create([
            'image_path' => 'gallery/plain-room.jpg',
            'alt_text' => null,
            'caption' => null,
            'is_published' => true,
            'published_at' => now(),
        ]);

        $this->assertDatabaseHas('gallery_items', [
            'image_path' => 'gallery/plain-room.jpg',
            'alt_text' => null,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('ng-gallery-grid', false)
            ->assertSee('alt=""', false)
            ->assertSee('View full-size photo 1 of 1', false)
            ->assertDontSee('from-black/80', false)
            ->assertDontSee('Cleaning photo', false);
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
            ->assertOk()
            ->assertSee('ng-gallery-bulk-upload', false);
    }
}
