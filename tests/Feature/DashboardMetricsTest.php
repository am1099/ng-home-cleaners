<?php

namespace Tests\Feature;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Models\User;
use App\Services\DashboardMetrics;
use App\Services\RevenueCalculator;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DashboardMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Service $regular;

    protected Service $deep;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->admin = User::factory()->create();
        $this->regular = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $this->deep = Service::query()->where('slug', 'deep-clean')->firstOrFail();
        $this->customer = Customer::query()->create([
            'first_name' => 'Casey',
            'last_name' => 'Ng',
            'phone_normalized' => '447503651400',
            'phone_display' => '07503 651400',
            'email' => 'casey.ng@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Market Square',
            'city' => 'Nottingham',
        ]);

        Carbon::setTestNow(Carbon::parse('2026-08-28 12:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_snapshot_calculations_match_database_truth(): void
    {
        $this->lead(QuoteRequestStatus::New, now()->subHour(), $this->regular);
        $this->lead(QuoteRequestStatus::New, now()->subDays(2), $this->deep);
        $this->lead(QuoteRequestStatus::QuoteSent, now()->subDays(3), $this->regular);
        $this->lead(QuoteRequestStatus::Won, now()->subDays(5), $this->regular, wonAt: now()->subDays(4));
        $this->lead(QuoteRequestStatus::Lost, now()->subDays(6), $this->deep, lostAt: now()->subDays(5));
        $this->lead(QuoteRequestStatus::Won, now()->subMonthNoOverflow()->day(10), $this->regular, wonAt: now()->subMonthNoOverflow()->day(11));
        $this->lead(QuoteRequestStatus::New, now()->subMonthNoOverflow()->day(15), $this->deep);

        // 4 regular, 3 deep → regular most requested
        $this->lead(QuoteRequestStatus::Contacted, now()->subDays(1), $this->regular);

        $upcoming = $this->booking(now()->addDays(2), BookingStatus::Scheduled, 10000);
        $this->booking(now()->addDays(4), BookingStatus::Scheduled, 12000);
        $this->booking(now()->subDays(1), BookingStatus::Completed, 8000, completedAt: now()->subDays(1));
        $this->booking(now()->subMonthNoOverflow()->day(20), BookingStatus::Completed, 7000, completedAt: now()->subMonthNoOverflow()->day(20));
        $this->booking(now()->addDays(1), BookingStatus::Cancelled, 5000);

        Payment::query()->create([
            'booking_id' => $upcoming->id,
            'amount_pence' => 4000,
            'type' => PaymentType::Deposit,
            'method' => PaymentMethod::Card,
            'paid_date' => now()->toDateString(),
        ]);

        $completedThisMonth = Booking::query()
            ->where('status', BookingStatus::Completed)
            ->whereDate('booking_date', now()->subDays(1)->toDateString())
            ->firstOrFail();

        Payment::query()->create([
            'booking_id' => $completedThisMonth->id,
            'amount_pence' => 8000,
            'type' => PaymentType::Full,
            'method' => PaymentMethod::Cash,
            'paid_date' => now()->subDays(1)->toDateString(),
        ]);

        Payment::query()->create([
            'booking_id' => $completedThisMonth->id,
            'amount_pence' => 1000,
            'type' => PaymentType::Refund,
            'method' => PaymentMethod::Cash,
            'paid_date' => now()->toDateString(),
        ]);

        $metrics = app(DashboardMetrics::class)->snapshot();

        $this->assertSame(3, $metrics['new_leads']);
        $this->assertSame(6, $metrics['leads_this_month']); // all except 2 last-month leads
        $this->assertSame(2, $metrics['leads_last_month']);
        $this->assertSame(1, $metrics['awaiting_response']);
        $this->assertSame(2, $metrics['upcoming_bookings']);
        $this->assertSame(1, $metrics['completed_jobs_this_month']);
        $this->assertSame(1, $metrics['completed_jobs_last_month']);

        // Revenue: 4000 + 8000 - 1000 = 11000 this month
        $this->assertSame(11000, $metrics['revenue_this_month_pence']);
        $this->assertSame(11000, $metrics['revenue_all_time_pence']);
        $this->assertSame(11000, app(RevenueCalculator::class)->totalPence());

        // Outstanding (non-cancelled):
        // scheduled 10000-4000=6000 + scheduled 12000 + completed this month 8000-7000=1000
        // + completed last month unpaid 7000 = 26000
        $this->assertSame(26000, $metrics['outstanding_balance_pence']);

        // Conversion this month: 1 won + 1 lost = 50%
        $this->assertSame(50.0, $metrics['conversion_rate']);
        $this->assertSame($this->regular->name, $metrics['most_requested_service']['name'] ?? null);
        $this->assertGreaterThanOrEqual(4, $metrics['most_requested_service']['count'] ?? 0);
    }

    public function test_dashboard_page_loads_with_metrics(): void
    {
        $this->lead(QuoteRequestStatus::New, now(), $this->regular);
        $booking = $this->booking(now()->addDays(3), BookingStatus::Scheduled, 15000);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 5000,
            'type' => PaymentType::Deposit,
            'method' => PaymentMethod::Card,
            'paid_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('At a glance', false)
            ->assertSee('New leads', false)
            ->assertSee('Revenue this month', false)
            ->assertSee('Recent leads', false)
            ->assertSee('Upcoming bookings', false)
            ->assertSee('Recent activity', false);
    }

    public function test_percent_and_count_change_descriptions(): void
    {
        $metrics = app(DashboardMetrics::class);

        $this->assertSame('Same as last month', $metrics->countChangeDescription(5, 5));
        $this->assertSame('Up 2 vs last month', $metrics->countChangeDescription(7, 5));
        $this->assertSame('Down 3 vs last month', $metrics->countChangeDescription(2, 5));
        $this->assertSame('Up from zero last month', $metrics->countChangeDescription(4, 0));

        $this->assertSame('Up 50% vs last month', $metrics->percentChangeDescription(150, 100));
        $this->assertSame('Down 25% vs last month', $metrics->percentChangeDescription(75, 100));
    }

    private function lead(
        QuoteRequestStatus $status,
        Carbon $submittedAt,
        Service $service,
        ?Carbon $wonAt = null,
        ?Carbon $lostAt = null,
    ): QuoteRequest {
        static $i = 0;
        $i++;

        return QuoteRequest::query()->create([
            'reference' => 'NG-D'.(1000 + $i),
            'customer_id' => $this->customer->id,
            'service_id' => $service->id,
            'source' => QuoteRequestSource::Web,
            'status' => $status,
            'first_name' => 'Casey',
            'last_name' => 'Ng',
            'phone' => '07503 651400',
            'email' => 'casey.ng@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '1 Market Square',
            'city' => 'Nottingham',
            'preferred_date' => $submittedAt->copy()->addWeek()->toDateString(),
            'arrival_window' => ArrivalWindow::Morning->value,
            'submitted_at' => $submittedAt,
            'won_at' => $wonAt,
            'lost_at' => $lostAt,
            'quote_sent_at' => $status === QuoteRequestStatus::QuoteSent ? $submittedAt->copy()->addDay() : null,
        ]);
    }

    private function booking(
        Carbon $date,
        BookingStatus $status,
        int $agreedPence,
        ?Carbon $completedAt = null,
    ): Booking {
        return Booking::query()->create([
            'customer_id' => $this->customer->id,
            'service_id' => $this->regular->id,
            'address_line1' => '1 Market Square',
            'city' => 'Nottingham',
            'postcode' => 'NG1 1AA',
            'booking_date' => $date->toDateString(),
            'arrival_window' => ArrivalWindow::Morning->value,
            'agreed_price_pence' => $agreedPence,
            'status' => $status->value,
            'completed_at' => $completedAt,
            'cancelled_at' => $status === BookingStatus::Cancelled ? now() : null,
        ]);
    }
}
