<?php

namespace Tests\Feature;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Filament\Pages\Analytics;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\GoogleReportingConnection;
use App\Models\Payment;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\Analytics\BusinessPerformance;
use App\Services\Analytics\EnquiryAttribution;
use App\Services\Analytics\ReportingPeriod;
use App\Services\Analytics\ServicePerformance;
use App\Services\GoogleAdsReport;
use Database\Seeders\CmsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class BusinessPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        Carbon::setTestNow(Carbon::parse('2026-11-27 12:00:00', 'Europe/London'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_booked_jobs_money_and_attention_use_real_records(): void
    {
        $customer = $this->customer();
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();

        $current = $this->booking($customer, $regular, BookingStatus::Scheduled, 12000, now());
        $this->booking($customer, $regular, BookingStatus::Cancelled, 9000, now());
        $this->booking($customer, $regular, BookingStatus::Completed, 8000, now()->subDays(80));

        Payment::query()->create([
            'booking_id' => $current->id,
            'amount_pence' => 10000,
            'type' => PaymentType::Full,
            'method' => PaymentMethod::Card,
            'paid_date' => now()->toDateString(),
        ]);
        Payment::query()->create([
            'booking_id' => $current->id,
            'amount_pence' => 2000,
            'type' => PaymentType::Refund,
            'method' => PaymentMethod::Card,
            'paid_date' => now()->toDateString(),
        ]);

        $this->quote($regular, QuoteRequestStatus::New, now(), QuoteRequestSource::Whatsapp, phone: null, email: null);
        $this->quote($regular, QuoteRequestStatus::Won, now()->subDays(40));

        $summary = app(BusinessPerformance::class)->summarise(ReportingPeriod::make(30));

        $this->assertSame(1, $summary['booked']['count']);
        $this->assertSame('£80.00', $summary['received']['amount']);
        $this->assertSame(1, $summary['quotes']['count']);
        $this->assertSame(1, $summary['attention']['count']);
        $this->assertStringContainsString('cannot be followed up', $summary['attention']['items'][0]['note']);
        $this->assertStringContainsString('more booking', $summary['booked']['change']);
    }

    public function test_service_rate_only_uses_quotes_from_the_same_period(): void
    {
        $customer = $this->customer();
        $regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $quote = $this->quote($regular, QuoteRequestStatus::Won, now());
        $this->booking($customer, $regular, BookingStatus::Scheduled, 15000, now(), $quote->id);
        $old = $this->quote($regular, QuoteRequestStatus::Won, now()->subDays(50));
        $this->booking($customer, $regular, BookingStatus::Scheduled, 5000, now(), $old->id);

        $row = collect(app(ServicePerformance::class)->rows(ReportingPeriod::make(30)))
            ->firstWhere('name', 'Regular clean');

        $this->assertSame(1, $row['quotes']);
        $this->assertSame(2, $row['bookings']);
        $this->assertSame(100, $row['rate']);
        $this->assertSame('£200.00', $row['agreed']);
    }

    public function test_staff_can_keep_their_own_section_order(): void
    {
        $user = User::factory()->create();

        Livewire::actingAs($user)
            ->test(Analytics::class)
            ->set('sectionOrder', ['profile', 'enquiries', 'bogus'])
            ->assertSet('sectionOrder.0', 'profile')
            ->assertSet('sectionOrder.1', 'enquiries');

        $this->assertSame('profile', $user->refresh()->analytics_layout[0]);
        $this->assertNotContains('bogus', $user->analytics_layout);
        $this->assertCount(7, $user->analytics_layout);
    }

    public function test_cost_per_booking_is_blank_when_there_are_no_bookings(): void
    {
        $result = app(EnquiryAttribution::class)->summarise(ReportingPeriod::make(30), 1500, true);

        $this->assertNull($result['cost_per_booking']);
        $this->assertNull($result['cost_per_whatsapp']);
    }

    public function test_google_ads_spend_is_converted_from_micros_and_actions_are_labelled(): void
    {
        config(['services.google.ads_customer_id' => '3391817713']);
        GoogleReportingConnection::query()->create([
            'refresh_token' => 'refresh-token',
            'connected_at' => now(),
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.test']),
            'https://googleads.googleapis.com/*' => Http::sequence()
                ->push([['results' => [['customer' => ['currencyCode' => 'GBP', 'descriptiveName' => 'NG']]]]])
                ->push([['results' => [['metrics' => [
                    'costMicros' => '15000000',
                    'clicks' => '6',
                    'impressions' => '38',
                    'conversions' => 4,
                ]]]]])
                ->push([['results' => [['campaign' => ['id' => '11', 'name' => 'Search', 'status' => 'ENABLED'], 'metrics' => [
                    'costMicros' => '15000000',
                    'clicks' => '6',
                    'impressions' => '38',
                    'conversions' => 4,
                ]]]]])
                ->push([['results' => [
                    ['segments' => ['conversionActionName' => 'WhatsApp click'], 'metrics' => ['conversions' => 3]],
                    ['segments' => ['conversionActionName' => 'Page view'], 'metrics' => ['conversions' => 1]],
                ]]])
                ->push([['results' => []]]),
        ]);

        $report = app(GoogleAdsReport::class)->forPeriod(ReportingPeriod::make(7));

        $this->assertTrue($report['available']);
        $this->assertSame('£15.00', $report['spend']);
        $this->assertSame(6, $report['clicks']);
        $this->assertSame(38, $report['impressions']);
        $this->assertSame(15.79, $report['ctr']);
        $this->assertSame('£2.50', $report['average_cpc']);
        $this->assertSame('WhatsApp click', $report['actions'][0]['kind']);
        $this->assertFalse($report['actions'][0]['review']);
        $this->assertTrue($report['actions'][1]['review']);
        $this->assertSame('£3.75', $report['campaigns'][0]['cost_per_conversion']);
        $this->assertNotSame('£0.00', $report['campaigns'][0]['cost_per_conversion']);
    }

    public function test_google_ads_errors_are_not_shown_as_zero_spend(): void
    {
        config(['services.google.ads_customer_id' => '3391817713']);
        GoogleReportingConnection::query()->create([
            'refresh_token' => 'refresh-token',
            'connected_at' => now(),
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.test']),
            'https://googleads.googleapis.com/*' => Http::response([
                'error' => ['status' => 'PERMISSION_DENIED', 'message' => 'CLOUD_PROJECT_NOT_APPROVED_FOR_PRODUCTION'],
            ], 403),
        ]);

        $report = app(GoogleAdsReport::class)->forPeriod(ReportingPeriod::make(7));

        $this->assertFalse($report['available']);
        $this->assertSame('not_approved', $report['state']);
        $this->assertNull($report['spend']);
        $this->assertNull($report['clicks']);
    }

    private function customer(): Customer
    {
        return Customer::query()->create([
            'first_name' => 'Casey',
            'last_name' => 'Ng',
            'phone_normalized' => '447503651400',
            'phone_display' => '07503 651400',
            'email' => 'casey.ng@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Market Square',
            'city' => 'Nottingham',
        ]);
    }

    private function booking(Customer $customer, Service $service, BookingStatus $status, int $agreed, Carbon $created, ?int $quoteId = null): Booking
    {
        $booking = Booking::query()->create([
            'customer_id' => $customer->id,
            'quote_request_id' => $quoteId,
            'service_id' => $service->id,
            'address_line1' => '1 Market Square',
            'city' => 'Nottingham',
            'postcode' => 'NG1 1AA',
            'booking_date' => $created->toDateString(),
            'arrival_window' => ArrivalWindow::Flexible->value,
            'agreed_price_pence' => $agreed,
            'status' => $status->value,
        ]);
        $booking->forceFill(['created_at' => $created, 'updated_at' => $created])->save();

        return $booking;
    }

    private function quote(
        Service $service,
        QuoteRequestStatus $status,
        Carbon $submitted,
        QuoteRequestSource $source = QuoteRequestSource::Web,
        ?string $phone = '07503651400',
        ?string $email = 'casey.ng@example.com',
    ): QuoteRequest {
        return QuoteRequest::query()->create([
            'reference' => 'NG-'.random_int(1000, 9999),
            'service_id' => $service->id,
            'source' => $source,
            'status' => $status,
            'first_name' => 'Casey',
            'phone' => $phone,
            'email' => $email,
            'postcode' => 'NG1 1AA',
            'submitted_at' => $submitted,
        ]);
    }
}
