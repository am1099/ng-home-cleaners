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
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertDontSee('aggregateRating', false);
        $response->assertSee('<h1', false);
        $response->assertSee('Professional Home Cleaners in Nottingham', false);
        $response->assertDontSee('—', false);
        $this->assertStringContainsString(' | ', $seo->title);
        $this->assertStringContainsString(parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost', $seo->canonical);
    }

    public function test_homepage_has_one_h1_about_nottingham_cleaners(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        preg_match_all('/<h1\b[^>]*>(.*?)<\/h1>/si', $html, $matches);
        $this->assertCount(1, $matches[0]);

        $h1 = html_entity_decode(strip_tags($matches[1][0]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $this->assertStringContainsString('Nottingham', $h1);
        $this->assertTrue(
            str_contains($h1, 'Cleaner') || str_contains($h1, 'Cleaning') || str_contains($h1, 'Cleaners'),
            'Homepage H1 should mention Cleaner/Cleaning',
        );
        $this->assertStringContainsString('Professional Home Cleaners', $h1);
        $this->assertStringNotContainsString('There are better uses for a Saturday morning', $h1);
    }

    public function test_homepage_json_ld_scripts_decode_and_include_website(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        preg_match_all('/<script type="application\/ld\+json">(.*?)<\/script>/si', $html, $matches);
        $this->assertNotEmpty($matches[1]);

        $types = [];
        foreach ($matches[1] as $raw) {
            $decoded = json_decode(html_entity_decode(trim($raw), ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
            $this->assertIsArray($decoded);
            $this->assertArrayNotHasKey('aggregateRating', $decoded);
            $types[] = $decoded['@type'] ?? null;
        }

        $flatTypes = collect($types)->flatten()->filter()->all();
        $this->assertContains('WebSite', $flatTypes);
        $this->assertTrue(
            in_array('LocalBusiness', $flatTypes, true)
                || collect($types)->contains(fn ($type) => is_array($type) && in_array('LocalBusiness', $type, true)),
        );
    }

    public function test_branded_titles_use_pipe_separator(): void
    {
        $seo = app(SeoService::class);

        $this->assertStringContainsString(' | ', $seo->forHome()->title);
        $this->assertStringContainsString('Cleaning Services in Nottingham', $seo->forServicesIndex()->title);
        $this->assertStringContainsString('Home Cleaning Areas in Nottingham', $seo->forAreasIndex()->title);
        $this->assertStringContainsString(' | ', $seo->forAbout()->title);
        $this->assertStringNotContainsString(' · ', $seo->forContact()->title);
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
            $response->assertSee(e($service->pageHeading()), false);
            $this->assertStringContainsString('Nottingham', $service->pageHeading());
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

        $response = $this->get(route('areas.show', $area))
            ->assertOk()
            ->assertSee('<title>'.e($seo->title).'</title>', false)
            ->assertSee('<meta name="description" content="'.e($seo->description).'">', false)
            ->assertSee(route('services'), false)
            ->assertSee($area->pageHeading(), false);

        $this->assertStringContainsString('Cleaning', $area->pageHeading());
        $this->assertStringContainsString($area->name, $area->pageHeading());
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

        $appHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: 'localhost';
        $response->assertSee($appHost, false);
        $response->assertDontSee('laravel.cloud', false);
        $response->assertDontSee('/admin', false);
        $response->assertDontSee('/get-a-quote/confirmation', false);
        $response->assertDontSee('/login', false);
    }

    public function test_robots_txt_disallows_admin_and_points_to_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /livewire', false)
            ->assertSee('Disallow: /get-a-quote/confirmation', false)
            ->assertSee('Sitemap: '.route('sitemap', absolute: true), false);
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

    public function test_admin_login_has_noindex(): void
    {
        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_homepage_faq_consolidates_payment_question(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('How and when do I pay?', false)
            ->assertDontSee('>How do I pay?<', false);
    }

    public function test_services_and_areas_index_h1_copy(): void
    {
        $this->get(route('services'))
            ->assertOk()
            ->assertSee('Cleaning Services in Nottingham', false);

        $this->get(route('areas'))
            ->assertOk()
            ->assertSee('Home Cleaning Across Nottingham', false);
    }

    public function test_organization_json_ld_does_not_invent_business_facts(): void
    {
        $json = app(SeoService::class)->organizationJsonLd();

        $this->assertArrayHasKey('name', $json);
        $this->assertArrayHasKey('telephone', $json);
        $this->assertArrayNotHasKey('aggregateRating', $json);
        $this->assertArrayNotHasKey('openingHours', $json);
        $this->assertArrayNotHasKey('openingHoursSpecification', $json);
        $this->assertArrayNotHasKey('geo', $json);

        $website = app(SeoService::class)->websiteJsonLd();
        $this->assertSame('WebSite', $website['@type']);
        $this->assertArrayHasKey('publisher', $website);
    }

    public function test_page_hero_fetchpriority_high_when_image_present(): void
    {
        $service = Service::query()->active()->firstOrFail();
        $service->forceFill(['hero_image' => 'services/seo-hero-test.jpg'])->save();

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('fetchpriority="high"', false);
    }
}
