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
use App\Services\BookingClashDetector;
use App\Services\BookingConversionService;
use App\Services\RevenueCalculator;
use App\Support\UkPostcode;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingsPaymentsRevenueTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Service $service;

    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CmsSeeder::class);
        $this->admin = User::factory()->create();
        $this->service = Service::query()->where('slug', 'regular-clean')->firstOrFail();
        $this->customer = Customer::query()->create([
            'first_name' => 'Sam',
            'last_name' => 'Brooks',
            'phone_normalized' => '447503651476',
            'phone_display' => '07503 651476',
            'email' => 'sam.brooks@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '10 Castle Gate',
            'city' => 'Nottingham',
        ]);
    }

    public function test_won_lead_converts_to_prefilled_booking(): void
    {
        $lead = $this->makeWonLead();

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.quote-requests.view', ['record' => $lead]))
            ->assertOk()
            ->assertSee('Convert to Booking', false);

        $booking = app(BookingConversionService::class)->createFromLead($lead);

        $this->assertSame($lead->customer_id, $booking->customer_id);
        $this->assertSame($lead->id, $booking->quote_request_id);
        $this->assertSame($lead->service_id, $booking->service_id);
        $this->assertSame('10 Castle Gate', $booking->address_line1);
        $this->assertSame('NG1 1AA', $booking->postcode);
        $this->assertTrue($booking->booking_date->equalTo($lead->preferred_date));
        $this->assertSame(ArrivalWindow::Morning, $booking->arrival_window);
        $this->assertSame(15000, $booking->agreed_price_pence);
        $this->assertSame(BookingStatus::Scheduled, $booking->status);
        $this->assertStringStartsWith('BK-', $booking->reference);
        $this->assertStringContainsString($lead->reference, (string) $booking->internal_notes);

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.quote-requests.view', ['record' => $lead]))
            ->assertOk()
            ->assertSee('View booking', false)
            ->assertDontSee('Convert to Booking', false);
    }

    public function test_deposit_and_balance_payments_update_outstanding_and_revenue(): void
    {
        $booking = $this->makeBooking(['agreed_price_pence' => 20000]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 5000,
            'type' => PaymentType::Deposit,
            'method' => PaymentMethod::Card,
            'paid_date' => now()->toDateString(),
            'reference' => 'DEP-1',
        ]);

        $booking->refresh();
        $this->assertSame(5000, $booking->paidPence());
        $this->assertSame(15000, $booking->outstandingPence());

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 15000,
            'type' => PaymentType::Balance,
            'method' => PaymentMethod::BankTransfer,
            'paid_date' => now()->toDateString(),
            'reference' => 'BAL-1',
        ]);

        $booking->refresh();
        $this->assertSame(20000, $booking->paidPence());
        $this->assertSame(0, $booking->outstandingPence());
        $this->assertFalse($booking->isOverpaid());

        $this->assertSame(20000, app(RevenueCalculator::class)->totalPence());
    }

    public function test_completed_booking_and_cancellation(): void
    {
        $booking = $this->makeBooking();

        $booking->markStatus(BookingStatus::Completed);
        $booking->refresh();
        $this->assertSame(BookingStatus::Completed, $booking->status);
        $this->assertNotNull($booking->completed_at);

        $booking->markStatus(BookingStatus::Cancelled);
        $booking->refresh();
        $this->assertSame(BookingStatus::Cancelled, $booking->status);
        $this->assertNotNull($booking->cancelled_at);
    }

    public function test_clash_warning_detects_conflicting_arrival_windows(): void
    {
        $date = now()->addDays(10)->toDateString();

        $this->makeBooking([
            'booking_date' => $date,
            'arrival_window' => ArrivalWindow::Morning->value,
        ]);

        $detector = app(BookingClashDetector::class);

        $this->assertNotNull($detector->warningMessage($date, ArrivalWindow::Morning));
        $this->assertNotNull($detector->warningMessage($date, ArrivalWindow::Flexible));
        $this->assertNull($detector->warningMessage($date, ArrivalWindow::Afternoon));

        $this->makeBooking([
            'booking_date' => $date,
            'arrival_window' => ArrivalWindow::Afternoon->value,
            'reference' => null,
        ]);

        $cancelled = $this->makeBooking([
            'booking_date' => $date,
            'arrival_window' => ArrivalWindow::Morning->value,
            'status' => BookingStatus::Cancelled->value,
            'reference' => null,
        ]);
        $this->assertSame(BookingStatus::Cancelled, $cancelled->status);

        $clashes = $detector->conflictingBookings($date, ArrivalWindow::Morning);
        $this->assertTrue($clashes->every(fn (Booking $b) => $b->status !== BookingStatus::Cancelled));
        $this->assertTrue($clashes->contains(fn (Booking $b) => $b->arrival_window === ArrivalWindow::Morning));
    }

    public function test_calendar_shows_bookings_and_hides_cancelled(): void
    {
        $date = now()->startOfMonth()->addDays(3);
        if ($date->isPast()) {
            $date = now()->startOfMonth()->addDays(14);
        }

        $visible = $this->makeBooking([
            'booking_date' => $date->toDateString(),
            'arrival_window' => ArrivalWindow::Afternoon->value,
        ]);

        $this->makeBooking([
            'booking_date' => $date->toDateString(),
            'arrival_window' => ArrivalWindow::Morning->value,
            'status' => BookingStatus::Cancelled->value,
            'reference' => null,
        ]);

        $this->actingAs($this->admin)
            ->get('/admin/booking-calendar?month='.$date->format('Y-m'))
            ->assertOk()
            ->assertSee($visible->customer->fullName(), false)
            ->assertSee($visible->service->name, false)
            ->assertSee('Afternoon', false)
            ->assertSee(UkPostcode::district($visible->postcode) ?? '', false);
    }

    public function test_revenue_excludes_quotes_and_accounts_for_refunds(): void
    {
        $lead = $this->makeWonLead();
        $this->assertSame(0, app(RevenueCalculator::class)->totalPence());

        $booking = app(BookingConversionService::class)->createFromLead($lead);
        $this->assertSame(0, app(RevenueCalculator::class)->totalPence());
        $this->assertSame(15000, $booking->outstandingPence());

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 10000,
            'type' => PaymentType::Deposit,
            'method' => PaymentMethod::Cash,
            'paid_date' => now()->toDateString(),
        ]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 2000,
            'type' => PaymentType::Refund,
            'method' => PaymentMethod::Cash,
            'paid_date' => now()->toDateString(),
        ]);

        $this->assertSame(8000, app(RevenueCalculator::class)->totalPence());
        $booking->refresh();
        $this->assertSame(8000, $booking->paidPence());
        $this->assertSame(7000, $booking->outstandingPence());
    }

    public function test_overpayment_is_detectable(): void
    {
        $booking = $this->makeBooking(['agreed_price_pence' => 10000]);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 12000,
            'type' => PaymentType::Full,
            'method' => PaymentMethod::Card,
            'paid_date' => now()->toDateString(),
        ]);

        $booking->refresh();
        $this->assertTrue($booking->isOverpaid());
        $this->assertSame(2000, $booking->overpaidPence());
        $this->assertSame(0, $booking->outstandingPence());
    }

    public function test_admin_booking_pages_are_reachable(): void
    {
        $booking = $this->makeBooking();

        $this->actingAs($this->admin)
            ->get('/admin/bookings')
            ->assertOk()
            ->assertSee($booking->reference, false);

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.bookings.view', ['record' => $booking]))
            ->assertOk()
            ->assertSee('Agreed', false)
            ->assertSee('Outstanding', false);

        $this->actingAs($this->admin)
            ->get('/admin/payments')
            ->assertOk();
    }

    public function test_non_won_lead_cannot_be_converted(): void
    {
        $lead = $this->makeWonLead();
        $lead->update([
            'status' => QuoteRequestStatus::New,
            'won_at' => null,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only won leads can be converted to bookings.');

        app(BookingConversionService::class)->createFromLead($lead->fresh());
    }

    public function test_double_conversion_is_blocked(): void
    {
        $lead = $this->makeWonLead();
        app(BookingConversionService::class)->createFromLead($lead);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This lead already has a booking.');

        app(BookingConversionService::class)->createFromLead($lead->fresh());
    }

    public function test_new_lead_does_not_show_convert_action(): void
    {
        $lead = $this->makeWonLead();
        $lead->update(['status' => QuoteRequestStatus::Contacted, 'won_at' => null]);

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.quote-requests.view', ['record' => $lead]))
            ->assertOk()
            ->assertDontSee('Convert to Booking', false);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function makeBooking(array $overrides = []): Booking
    {
        return Booking::query()->create(array_merge([
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'address_line1' => '10 Castle Gate',
            'city' => 'Nottingham',
            'postcode' => 'NG1 1AA',
            'booking_date' => now()->addDays(7)->toDateString(),
            'arrival_window' => ArrivalWindow::Morning->value,
            'agreed_price_pence' => 18000,
            'status' => BookingStatus::Scheduled->value,
        ], $overrides));
    }

    private function makeWonLead(): QuoteRequest
    {
        return QuoteRequest::query()->create([
            'reference' => 'NG-9001',
            'customer_id' => $this->customer->id,
            'service_id' => $this->service->id,
            'source' => QuoteRequestSource::Web,
            'status' => QuoteRequestStatus::Won,
            'first_name' => 'Sam',
            'last_name' => 'Brooks',
            'phone' => '07503 651476',
            'email' => 'sam.brooks@example.com',
            'postcode' => 'NG1 1AA',
            'address_line1' => '10 Castle Gate',
            'city' => 'Nottingham',
            'preferred_date' => now()->addDays(12)->toDateString(),
            'arrival_window' => ArrivalWindow::Morning->value,
            'final_quote_amount_pence' => 15000,
            'internal_notes' => 'Agreed after site visit.',
            'submitted_at' => now(),
            'won_at' => now(),
        ]);
    }
}
