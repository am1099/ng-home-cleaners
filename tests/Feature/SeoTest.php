<?php

namespace Tests\Feature;

use App\Http\Middleware\ForceCanonicalUrls;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\ServiceArea;
use App\Models\User;
use App\Services\SeoService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_homepage_renders_unique_metadata_and_json_ld(): void
    {
        $seo = app(SeoService::class)->forHome();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<title>'.e($seo->title).'</title>', false);
        $response->assertSee('<meta name="description" content="'.e($seo->description).'">', false);
        $response->assertSee('<link rel="canonical" href="'.e($seo->canonical).'">', false);
        $response->assertSee('property="og:title"', false);
        $response->assertSee('property="og:description"', false);
        $response->assertSee('name="twitter:title"', false);
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":["LocalBusiness","HomeAndConstructionBusiness"]', false);
        $response->assertDontSee('aggregateRating', false);
        $response->assertSee('<h1', false);
        $response->assertDontSee('—', false);
    }

    public function test_every_active_service_has_crm_or_fallback_metadata(): void
    {
        foreach (Service::query()->active()->get() as $service) {
            $seo = app(SeoService::class)->forService($service);

            $response = $this->get(route('services.show', $service));

            $response->assertOk();
            $response->assertSee('<title>'.e($seo->title).'</title>', false);
            $response->assertSee('<meta name="description" content="'.e($seo->description).'">', false);
            $response->assertSee('<link rel="canonical" href="'.e(route('services.show', $service, absolute: true)).'">', false);
            $response->assertSee('"@type":"BreadcrumbList"', false);
            $this->assertNotSame('', $seo->title);
            $this->assertNotSame('', $seo->description);
        }
    }

    public function test_example_area_page_uses_area_seo_values(): void
    {
        $area = ServiceArea::query()->active()->where('slug', 'city-centre')->first()
            ?? ServiceArea::query()->active()->firstOrFail();

        $seo = app(SeoService::class)->forArea($area);

        $this->assertStringContainsString($area->name, $seo->title);

        $this->get(route('areas.show', $area))
            ->assertOk()
            ->assertSee('<title>'.e($seo->title).'</title>', false)
            ->assertSee('<meta name="description" content="'.e($seo->description).'">', false)
            ->assertSee(route('services'), false);
    }

    public function test_about_and_contact_pages_have_metadata(): void
    {
        $about = app(SeoService::class)->forAbout();
        $contact = app(SeoService::class)->forContact();

        $this->get(route('about'))
            ->assertOk()
            ->assertSee('<title>'.e($about->title).'</title>', false)
            ->assertSee('<meta name="description" content="'.e($about->description).'">', false);

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('<title>'.e($contact->title).'</title>', false)
            ->assertSee('<meta name="description" content="'.e($contact->description).'">', false)
            ->assertSee('Browse services', false);
    }

    public function test_sitemap_lists_only_indexable_public_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<loc>'.e(route('home', absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('quote', absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('reviews', absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('move-in-out', absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('services', absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('areas', absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('about', absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('contact', absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('legal.privacy', absolute: true)).'</loc>', false);

        $service = Service::query()->active()->firstOrFail();
        $area = ServiceArea::query()->active()->firstOrFail();
        $response->assertSee('<loc>'.e(route('services.show', $service, absolute: true)).'</loc>', false);
        $response->assertSee('<loc>'.e(route('areas.show', $area, absolute: true)).'</loc>', false);

        $response->assertDontSee('/admin', false);
        $response->assertDontSee('/get-a-quote/confirmation', false);
        $response->assertDontSee('/login', false);
    }

    public function test_robots_txt_disallows_admin_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /get-a-quote/confirmation', false)
            ->assertSee('Sitemap: '.url('/sitemap.xml'), false);
    }

    public function test_quote_confirmation_is_noindex(): void
    {
        $admin = User::factory()->create();
        $this->actingAs($admin);

        // Confirmation needs a real quote request reference; create a minimal one via DB.
        $service = Service::query()->active()->firstOrFail();
        $lead = QuoteRequest::query()->create([
            'reference' => 'NG-SEO1',
            'customer_id' => Customer::query()->create([
                'first_name' => 'Seo',
                'last_name' => 'Test',
                'phone_normalized' => '447500000001',
                'phone_display' => '07500 000001',
                'email' => 'seo@example.com',
                'postcode' => 'NG1 1AA',
                'address_line1' => '1 Test Street',
                'city' => 'Nottingham',
            ])->id,
            'service_id' => $service->id,
            'source' => 'web',
            'status' => 'new',
            'first_name' => 'Seo',
            'last_name' => 'Test',
            'phone' => '07500 000001',
            'email' => 'seo@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Test Street',
            'city' => 'Nottingham',
            'preferred_date' => now()->addWeek()->toDateString(),
            'arrival_window' => 'flexible',
            'submitted_at' => now(),
        ]);

        $this->get(route('quote.confirmation', $lead->reference))
            ->assertOk()
            ->assertSee('name="robots" content="noindex,nofollow"', false);
    }

    public function test_custom_404_and_trailing_slash_redirect(): void
    {
        $this->get('/this-page-does-not-exist-seo')
            ->assertNotFound()
            ->assertSee('We cannot find that page.', false)
            ->assertSee('name="robots" content="noindex,follow"', false);

        $request = Request::create('https://example.test/about/', 'GET');
        $response = (new ForceCanonicalUrls)->handle(
            $request,
            fn () => response('should-not-run'),
        );

        $this->assertTrue($response->isRedirection());
        $this->assertSame(301, $response->getStatusCode());
        $this->assertSame(url('/about'), $response->headers->get('Location'));
    }

    public function test_sitemap_excludes_inactive_services_and_areas(): void
    {
        $service = Service::query()->active()->firstOrFail();
        $area = ServiceArea::query()->active()->firstOrFail();

        $service->update(['is_active' => false]);
        $area->update(['is_active' => false]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertDontSee(route('services.show', $service, absolute: true), false);
        $response->assertDontSee(route('areas.show', $area, absolute: true), false);
    }

    public function test_quote_page_has_title_and_canonical(): void
    {
        $seo = app(SeoService::class)->forQuote();

        $this->get(route('quote'))
            ->assertOk()
            ->assertSee('<title>'.e($seo->title).'</title>', false)
            ->assertSee('<link rel="canonical" href="'.e($seo->canonical).'">', false);
    }

    public function test_admin_login_is_not_in_sitemap(): void
    {
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertDontSee('/admin', false)
            ->assertDontSee('/get-a-quote/confirmation', false);
    }

    public function test_organization_json_ld_does_not_invent_business_facts(): void
    {
        $json = app(SeoService::class)->organizationJsonLd();

        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('telephone', $json);
        $this->assertArrayNotHasKey('aggregateRating', $json);
        $this->assertArrayNotHasKey('openingHours', $json);
        $this->assertArrayNotHasKey('openingHoursSpecification', $json);
    }
}
