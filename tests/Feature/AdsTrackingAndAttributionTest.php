<?php

namespace Tests\Feature;

use App\Data\WizardSubmissionData;
use App\Enums\BookingStatus;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\AdAttribution;
use App\Services\GoogleAdsOfflineConversionExport;
use App\Services\QuoteRequestService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdsTrackingAndAttributionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
    }

    public function test_quote_links_do_not_render_raw_blade_in_onclick(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('@js($location)', false)
            ->assertSee('data-ng-track="quote_started"', false)
            ->assertSee('data-ng-track-location="home_hero"', false);

        $this->get('/contact')
            ->assertOk()
            ->assertDontSee('@js($location)', false)
            ->assertSee('data-ng-track="quote_started"', false);

        $this->get('/get-a-quote')
            ->assertOk()
            ->assertDontSee('@js($location)', false)
            ->assertSee('window.location.assign(url)', false)
            ->assertDontSee("window.open(url, '_blank')", false)
            ->assertSee("gtag('consent', 'default'", false)
            ->assertSee('ads_data_redaction', false);
    }

    public function test_click_id_is_remembered_for_the_visit_but_not_cookied_before_consent(): void
    {
        $this->get('/?gclid=CjwKCAjwTEST1234&utm_source=google')
            ->assertOk()
            ->assertCookieMissing(AdAttribution::COOKIE);

        $stored = session(AdAttribution::SESSION_KEY);

        $this->assertSame('CjwKCAjwTEST1234', $stored['latest']['gclid']);
        $this->assertSame('google', $stored['latest']['utm_source']);
    }

    public function test_invalid_click_ids_are_ignored(): void
    {
        $this->get('/?gclid=bad%20id')
            ->assertOk()
            ->assertCookieMissing(AdAttribution::COOKIE);

        $this->assertNull(session(AdAttribution::SESSION_KEY));
    }

    public function test_accepted_cookies_allow_a_persistent_attribution_cookie(): void
    {
        $this->withUnencryptedCookie(AdAttribution::CONSENT_COOKIE, 'accepted')
            ->get('/?gclid=CjwKCAjwTEST1234')
            ->assertOk()
            ->assertCookie(AdAttribution::COOKIE);
    }

    public function test_quote_stores_click_id_only_after_cookie_consent(): void
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $touch = [
            'gclid' => 'CjwKCAjwTEST1234',
            'utm_source' => 'google',
            'landing_page' => '/get-a-quote?gclid=CjwKCAjwTEST1234',
            'clicked_at' => now()->toIso8601String(),
        ];

        $session = app('session.store');
        $session->put(AdAttribution::SESSION_KEY, ['first' => $touch, 'latest' => $touch]);
        request()->setLaravelSession($session);

        $withoutConsent = $this->makeQuote($service->id);
        $this->assertNull($withoutConsent->ad_attribution);

        request()->cookies->set(AdAttribution::CONSENT_COOKIE, 'accepted');

        $withConsent = $this->makeQuote($service->id);

        $this->assertSame('CjwKCAjwTEST1234', $withConsent->ad_attribution['latest']['gclid']);
        $this->assertNull($withConsent->first_name);
        $this->assertStringContainsString('Quote reference: '.$withConsent->reference, app(QuoteRequestService::class)->whatsappMessage($withConsent));
        $this->assertStringNotContainsString('CjwKCAjwTEST1234', app(QuoteRequestService::class)->whatsappMessage($withConsent));
    }

    public function test_offline_export_includes_only_confirmed_bookings_once(): void
    {
        $ready = $this->bookingWithClick('NG-READY', 12000, BookingStatus::Scheduled);
        $this->bookingWithClick('NG-CANCELLED', 12000, BookingStatus::Cancelled);
        $this->bookingWithClick('NG-FREE', 0, BookingStatus::Scheduled);

        $export = app(GoogleAdsOfflineConversionExport::class);
        $pending = $export->pending();

        $this->assertCount(1, $pending);
        $this->assertSame($ready->id, $pending->first()->id);

        $csv = $export->toCsv($pending);

        $this->assertStringContainsString('Parameters:TimeZone=Europe/London', $csv);
        $this->assertStringContainsString('CjwKCAjwTEST1234', $csv);
        $this->assertStringContainsString('NG - Booked job', $csv);
        $this->assertStringContainsString('120.00', $csv);
        $this->assertStringContainsString('GBP', $csv);
        $this->assertStringContainsString($ready->reference, $csv);
        $this->assertStringNotContainsString('NG-CANCELLED', $csv);

        $this->actingAs(User::factory()->create())
            ->get(route('crm.google-ads-booked-jobs'))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $this->assertNotNull($ready->fresh()->ads_offline_exported_at);
        $this->assertCount(0, $export->pending());
    }

    public function test_offline_export_requires_an_admin_session(): void
    {
        $this->get(route('crm.google-ads-booked-jobs'))->assertRedirect('/admin/login');
    }

    private function makeQuote(int $serviceId): QuoteRequest
    {
        return app(QuoteRequestService::class)->createFromWizard(
            new WizardSubmissionData(
                serviceId: $serviceId,
                frequency: 'one_off',
                propertyType: 'house',
                bedrooms: 2,
                splitLevelFlat: false,
                floors: 2,
                bathrooms: 1,
                wcs: 0,
                kitchens: 1,
                receptionRooms: 1,
                extraRooms: [],
                propertyStatus: null,
                conditionFlags: [],
                conditionNotes: '',
                addonIds: [],
                preferredDate: '',
                arrivalWindow: '',
                firstName: '',
                lastName: '',
                phone: '',
                email: '',
                postcode: '',
                addressLine1: '',
                addressLine2: null,
                city: '',
                parkingNotes: '',
                accessNotes: '',
            ),
            QuoteRequestSource::Whatsapp,
            true,
        );
    }

    private function bookingWithClick(string $reference, int $agreedPence, BookingStatus $status): Booking
    {
        $service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $customer = Customer::query()->create([
            'first_name' => 'Pat',
            'last_name' => 'Customer',
            'phone_normalized' => '07503651476',
            'phone_display' => '07503 651476',
            'email' => strtolower($reference).'@example.com',
        ]);
        $quote = QuoteRequest::query()->create([
            'reference' => $reference,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'source' => QuoteRequestSource::Whatsapp,
            'status' => QuoteRequestStatus::Won,
            'phone' => '07503 651476',
            'bedrooms' => 2,
            'bathrooms' => 1,
            'selections_snapshot' => [],
            'pricing_snapshot' => [],
            'ad_attribution' => [
                'first' => [
                    'gclid' => 'CjwKCAjwTEST1234',
                    'clicked_at' => now()->subHour()->toIso8601String(),
                ],
                'latest' => [
                    'gclid' => 'CjwKCAjwTEST1234',
                    'clicked_at' => now()->subHour()->toIso8601String(),
                ],
            ],
            'submitted_at' => now()->subHour(),
        ]);

        return Booking::query()->create([
            'customer_id' => $customer->id,
            'quote_request_id' => $quote->id,
            'service_id' => $service->id,
            'address_line1' => '1 Test Street',
            'postcode' => 'NG1 1AA',
            'agreed_price_pence' => $agreedPence,
            'booking_date' => now()->addWeek()->toDateString(),
            'arrival_window' => 'flexible',
            'status' => $status,
        ]);
    }
}
