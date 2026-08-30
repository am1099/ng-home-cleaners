<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\QuoteRequestStatus;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\QuoteRequest;
use App\Models\User;
use App\Services\DashboardMetrics;
use App\Services\RevenueCalculator;
use Database\Seeders\CmsSeeder;
use Database\Seeders\CrmDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CrmDemoDashboardVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seed_figures_match_dashboard_metrics(): void
    {
        $this->seed(CmsSeeder::class);
        $this->seed(CrmDemoSeeder::class);

        $metrics = app(DashboardMetrics::class)->snapshot();

        $this->assertSame(
            QuoteRequest::query()->where('status', QuoteRequestStatus::New)->count(),
            $metrics['new_leads'],
        );

        $this->assertSame(
            QuoteRequest::query()->where('status', QuoteRequestStatus::QuoteSent)->count(),
            $metrics['awaiting_response'],
        );

        $this->assertSame(
            Booking::query()
                ->where('status', BookingStatus::Scheduled)
                ->whereDate('booking_date', '>=', now()->toDateString())
                ->count(),
            $metrics['upcoming_bookings'],
        );

        $this->assertSame(
            app(RevenueCalculator::class)->totalPence(now()->startOfMonth(), now()->endOfMonth()),
            $metrics['revenue_this_month_pence'],
        );

        $this->assertSame(
            (int) Payment::query()->sum('amount_pence'),
            $metrics['revenue_all_time_pence'],
        );

        $this->assertSame(
            app(DashboardMetrics::class)->outstandingBalancePence(),
            $metrics['outstanding_balance_pence'],
        );

        $this->assertNotNull($metrics['most_requested_service']);
        $this->assertSame(
            QuoteRequest::query()
                ->selectRaw('service_id, count(*) as aggregate')
                ->groupBy('service_id')
                ->orderByDesc('aggregate')
                ->value('service_id'),
            $metrics['most_requested_service']['id'],
        );

        $this->assertGreaterThan(0, $metrics['leads_this_month']);
        $this->assertGreaterThan(0, $metrics['revenue_this_month_pence']);
        $this->assertGreaterThan(0, $metrics['outstanding_balance_pence']);

        $admin = User::factory()->create();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('New leads', false)
            ->assertSee((string) $metrics['new_leads'], false)
            ->assertSee('Awaiting response', false);
    }
}
