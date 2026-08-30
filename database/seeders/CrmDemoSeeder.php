<?php

namespace Database\Seeders;

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
use App\Services\BookingReferenceGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Realistic local CRM sample data for dashboard verification.
 * Skipped in production.
 */
class CrmDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            return;
        }

        if (QuoteRequest::query()->exists() || Booking::query()->exists()) {
            $this->command?->warn('CRM demo data skipped — leads or bookings already exist.');

            return;
        }

        $services = Service::query()->where('is_active', true)->orderBy('sort_order')->get();
        if ($services->isEmpty()) {
            $this->command?->warn('CRM demo data skipped — no active services. Run CmsSeeder first.');

            return;
        }

        $regular = $services->firstWhere('slug', 'regular-clean') ?? $services->first();
        $deep = $services->firstWhere('slug', 'deep-clean') ?? $services->skip(1)->first() ?? $regular;
        $endOfTenancy = $services->firstWhere('slug', 'end-of-tenancy') ?? $services->skip(2)->first() ?? $regular;

        $now = now();

        $customers = collect([
            $this->customer('Amelia', 'Hart', '07503110001', 'amelia.hart@example.com', 'NG1 2AB', '14 Bridlesmith Gate'),
            $this->customer('Noah', 'Patel', '07503110002', 'noah.patel@example.com', 'NG5 3CD', '22 Mansfield Road'),
            $this->customer('Isla', 'Murphy', '07503110003', 'isla.murphy@example.com', 'NG7 4EF', '8 Derby Road'),
            $this->customer('Oliver', 'Chen', '07503110004', 'oliver.chen@example.com', 'NG2 5GH', '3 Trent Bridge'),
            $this->customer('Freya', 'Walsh', '07503110005', 'freya.walsh@example.com', 'NG8 6IJ', '19 Wollaton Vale'),
            $this->customer('Arthur', 'Singh', '07503110006', null, 'NG3 7KL', '41 Carlton Hill'),
        ]);

        $leadSpecs = [
            // New
            ['cust' => 0, 'service' => $regular, 'status' => QuoteRequestStatus::New, 'when' => $now->copy()->subHours(6), 'source' => QuoteRequestSource::Web, 'quote' => null],
            ['cust' => 1, 'service' => $deep, 'status' => QuoteRequestStatus::New, 'when' => $now->copy()->subDay(), 'source' => QuoteRequestSource::Whatsapp, 'quote' => null],
            // Contacted
            ['cust' => 2, 'service' => $regular, 'status' => QuoteRequestStatus::Contacted, 'when' => $now->copy()->subDays(2), 'source' => QuoteRequestSource::Phone, 'quote' => null],
            // Quote sent (awaiting response)
            ['cust' => 3, 'service' => $endOfTenancy, 'status' => QuoteRequestStatus::QuoteSent, 'when' => $now->copy()->subDays(3), 'source' => QuoteRequestSource::Web, 'quote' => 18500],
            ['cust' => 4, 'service' => $deep, 'status' => QuoteRequestStatus::QuoteSent, 'when' => $now->copy()->subDays(4), 'source' => QuoteRequestSource::Web, 'quote' => 22000],
            // Won this month → bookings
            ['cust' => 0, 'service' => $regular, 'status' => QuoteRequestStatus::Won, 'when' => $now->copy()->subDays(8), 'source' => QuoteRequestSource::Web, 'quote' => 9500, 'won' => $now->copy()->subDays(6)],
            ['cust' => 1, 'service' => $deep, 'status' => QuoteRequestStatus::Won, 'when' => $now->copy()->subDays(12), 'source' => QuoteRequestSource::Phone, 'quote' => 16000, 'won' => $now->copy()->subDays(10)],
            ['cust' => 2, 'service' => $regular, 'status' => QuoteRequestStatus::Won, 'when' => $now->copy()->subDays(15), 'source' => QuoteRequestSource::Web, 'quote' => 11000, 'won' => $now->copy()->subDays(14)],
            // Lost this month
            ['cust' => 5, 'service' => $deep, 'status' => QuoteRequestStatus::Lost, 'when' => $now->copy()->subDays(9), 'source' => QuoteRequestSource::Web, 'quote' => 19000, 'lost' => $now->copy()->subDays(7)],
            // Last month leads (for comparisons)
            ['cust' => 3, 'service' => $regular, 'status' => QuoteRequestStatus::Won, 'when' => $now->copy()->subMonthNoOverflow()->day(8)->setTime(10, 0), 'source' => QuoteRequestSource::Web, 'quote' => 9000, 'won' => $now->copy()->subMonthNoOverflow()->day(10)->setTime(11, 0)],
            ['cust' => 4, 'service' => $regular, 'status' => QuoteRequestStatus::Lost, 'when' => $now->copy()->subMonthNoOverflow()->day(12)->setTime(9, 0), 'source' => QuoteRequestSource::Whatsapp, 'quote' => null, 'lost' => $now->copy()->subMonthNoOverflow()->day(14)->setTime(16, 0)],
            ['cust' => 5, 'service' => $endOfTenancy, 'status' => QuoteRequestStatus::Won, 'when' => $now->copy()->subMonthNoOverflow()->day(18)->setTime(14, 0), 'source' => QuoteRequestSource::Phone, 'quote' => 17500, 'won' => $now->copy()->subMonthNoOverflow()->day(20)->setTime(10, 0)],
            // Extra regular-clean requests so it is most requested
            ['cust' => 0, 'service' => $regular, 'status' => QuoteRequestStatus::Contacted, 'when' => $now->copy()->subDays(5), 'source' => QuoteRequestSource::Manual, 'quote' => null],
            ['cust' => 1, 'service' => $regular, 'status' => QuoteRequestStatus::New, 'when' => $now->copy()->subHours(30), 'source' => QuoteRequestSource::Web, 'quote' => null],
        ];

        $reference = 9100;
        $leads = [];

        foreach ($leadSpecs as $spec) {
            /** @var Customer $customer */
            $customer = $customers[$spec['cust']];
            $status = $spec['status'];
            $when = $spec['when'];

            $leads[] = QuoteRequest::query()->create([
                'reference' => 'NG-'.($reference++),
                'customer_id' => $customer->id,
                'service_id' => $spec['service']->id,
                'source' => $spec['source'],
                'status' => $status,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'phone' => $customer->phone_display,
                'email' => $customer->email,
                'postcode' => $customer->postcode,
                'address_line1' => $customer->address_line1,
                'city' => $customer->city,
                'preferred_date' => $when->copy()->addDays(7)->toDateString(),
                'arrival_window' => ArrivalWindow::cases()[array_rand(ArrivalWindow::cases())]->value,
                'final_quote_amount_pence' => $spec['quote'],
                'guide_estimate_headline' => $spec['quote'] ? null : 'Guide from £'.((int) (($spec['service']->id % 5) + 8) * 10),
                'guide_estimate_min_pence' => 8000,
                'guide_estimate_max_pence' => 14000,
                'is_numeric_estimate' => true,
                'submitted_at' => $when,
                'contacted_at' => in_array($status, [QuoteRequestStatus::Contacted, QuoteRequestStatus::QuoteSent, QuoteRequestStatus::Won, QuoteRequestStatus::Lost], true)
                    ? $when->copy()->addHours(4)
                    : null,
                'quote_sent_at' => in_array($status, [QuoteRequestStatus::QuoteSent, QuoteRequestStatus::Won], true)
                    ? $when->copy()->addDay()
                    : null,
                'won_at' => $spec['won'] ?? null,
                'lost_at' => $spec['lost'] ?? null,
                'internal_notes' => 'Demo seed lead.',
            ]);
        }

        $refs = app(BookingReferenceGenerator::class);

        // Upcoming scheduled
        $upcomingA = $this->booking($refs->next(), $customers[0], $regular, $leads[5], $now->copy()->addDays(3), ArrivalWindow::Morning, 9500, BookingStatus::Scheduled);
        $upcomingB = $this->booking($refs->next(), $customers[1], $deep, $leads[6], $now->copy()->addDays(5), ArrivalWindow::Afternoon, 16000, BookingStatus::Scheduled);
        $upcomingC = $this->booking($refs->next(), $customers[2], $regular, $leads[7], $now->copy()->addDays(9), ArrivalWindow::Flexible, 11000, BookingStatus::Scheduled);

        // Completed this month
        $completedA = $this->booking(
            $refs->next(),
            $customers[3],
            $regular,
            null,
            $now->copy()->subDays(4),
            ArrivalWindow::Morning,
            9000,
            BookingStatus::Completed,
            $now->copy()->subDays(4)->setTime(15, 0),
        );

        // Completed last month
        $completedLast = $this->booking(
            $refs->next(),
            $customers[4],
            $endOfTenancy,
            $leads[11] ?? null,
            $now->copy()->subMonthNoOverflow()->day(22),
            ArrivalWindow::Afternoon,
            17500,
            BookingStatus::Completed,
            $now->copy()->subMonthNoOverflow()->day(22)->setTime(16, 30),
        );

        // Cancelled (should not affect upcoming / outstanding)
        $this->booking($refs->next(), $customers[5], $deep, null, $now->copy()->addDays(2), ArrivalWindow::Morning, 15000, BookingStatus::Cancelled, null, $now->copy()->subDay());

        // Payments — revenue this month
        $this->payment($upcomingA, 3000, PaymentType::Deposit, PaymentMethod::Card, $now->copy()->subDays(5));
        $this->payment($upcomingB, 5000, PaymentType::Deposit, PaymentMethod::BankTransfer, $now->copy()->subDays(8));
        $this->payment($completedA, 9000, PaymentType::Full, PaymentMethod::Cash, $now->copy()->subDays(4));

        // Outstanding on upcoming C (full agreed unpaid) and partial on A/B
        // Last month revenue
        $this->payment($completedLast, 10000, PaymentType::Deposit, PaymentMethod::Card, $now->copy()->subMonthNoOverflow()->day(19));
        $this->payment($completedLast, 7500, PaymentType::Balance, PaymentMethod::Card, $now->copy()->subMonthNoOverflow()->day(22));

        // Small refund this month (reduces revenue)
        $this->payment($completedA, 500, PaymentType::Refund, PaymentMethod::Cash, $now->copy()->subDays(2));

        $this->command?->info('CRM demo data seeded for dashboard verification.');
    }

    private function customer(
        string $first,
        string $last,
        string $phone,
        ?string $email,
        string $postcode,
        string $address,
    ): Customer {
        return Customer::query()->create([
            'first_name' => $first,
            'last_name' => $last,
            'phone_normalized' => '44'.ltrim(preg_replace('/\D+/', '', $phone) ?? '', '0'),
            'phone_display' => $phone,
            'email' => $email,
            'postcode' => $postcode,
            'address_line1' => $address,
            'city' => 'Nottingham',
            'notes' => 'Demo customer.',
        ]);
    }

    private function booking(
        string $reference,
        Customer $customer,
        Service $service,
        ?QuoteRequest $lead,
        Carbon $date,
        ArrivalWindow $window,
        int $agreedPence,
        BookingStatus $status,
        ?Carbon $completedAt = null,
        ?Carbon $cancelledAt = null,
    ): Booking {
        return Booking::query()->create([
            'reference' => $reference,
            'customer_id' => $customer->id,
            'quote_request_id' => $lead?->id,
            'service_id' => $service->id,
            'address_line1' => $customer->address_line1,
            'city' => $customer->city,
            'postcode' => $customer->postcode,
            'booking_date' => $date->toDateString(),
            'arrival_window' => $window->value,
            'agreed_price_pence' => $agreedPence,
            'status' => $status->value,
            'internal_notes' => 'Demo seed booking.',
            'completed_at' => $completedAt,
            'cancelled_at' => $cancelledAt,
            'created_at' => $date->copy()->subDays(3),
            'updated_at' => $date->copy()->subDays(1),
        ]);
    }

    private function payment(
        Booking $booking,
        int $amountPence,
        PaymentType $type,
        PaymentMethod $method,
        Carbon $paidDate,
    ): Payment {
        return Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => $amountPence,
            'type' => $type,
            'method' => $method,
            'paid_date' => $paidDate->toDateString(),
            'reference' => strtoupper($type->value).'-'.$booking->id,
            'notes' => 'Demo payment.',
            'created_at' => $paidDate,
            'updated_at' => $paidDate,
        ]);
    }
}
