<?php

namespace Tests\Feature;

use App\Actions\CreateInvoiceFromBooking;
use App\Actions\IssueInvoice;
use App\Actions\QueueInvoiceEmail;
use App\Actions\RecalculateInvoiceTotals;
use App\Actions\VoidInvoice;
use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Jobs\SendInvoiceMailJob;
use App\Mail\CustomerInvoiceMail;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\InvoiceNumberGenerator;
use App\Services\InvoicePdfService;
use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class InvoicesTest extends TestCase
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

        Storage::fake('local');
        config(['filesystems.invoice' => 'local']);
        config(['laravel-pdf.driver' => 'dompdf']);
    }

    public function test_create_draft_from_booking_prefills_snapshot_without_number(): void
    {
        $booking = $this->makeBooking(['agreed_price_pence' => 22500]);

        $invoice = app(CreateInvoiceFromBooking::class)->handle($booking, $this->admin);

        $this->assertSame(InvoiceStatus::Draft, $invoice->status);
        $this->assertNull($invoice->invoice_number);
        $this->assertSame($booking->id, $invoice->booking_id);
        $this->assertSame($this->customer->id, $invoice->customer_id);
        $this->assertSame('Sam Brooks', $invoice->customer_name);
        $this->assertSame('sam.brooks@example.com', $invoice->customer_email);
        $this->assertSame('10 Castle Gate', $invoice->billing_address_line1);
        $this->assertSame($booking->reference, $invoice->booking_reference);
        $this->assertSame(22500, $invoice->total_pence);
        $this->assertCount(1, $invoice->items);
        $this->assertSame(22500, (int) $invoice->items->first()->line_total_pence);
        $this->assertDatabaseMissing('invoice_number_counters', ['year' => now()->year]);
    }

    public function test_draft_does_not_consume_invoice_number(): void
    {
        app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin);

        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseMissing('invoice_number_counters', ['year' => now()->year]);
    }

    public function test_issue_allocates_number_and_stores_private_pdf(): void
    {
        $booking = $this->makeBooking(['agreed_price_pence' => 22500]);
        $draft = app(CreateInvoiceFromBooking::class)->handle($booking, $this->admin);

        $invoice = app(IssueInvoice::class)->handle($draft);

        $year = (int) now()->year;
        $this->assertSame(sprintf('NG-%d-0001', $year), $invoice->invoice_number);
        $this->assertSame(InvoiceStatus::Issued, $invoice->status);
        $this->assertNotNull($invoice->issued_at);
        $this->assertSame('local', $invoice->pdf_disk);
        $this->assertSame("invoices/{$year}/{$invoice->invoice_number}.pdf", $invoice->pdf_path);
        Storage::disk('local')->assertExists($invoice->pdf_path);

        $second = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin),
        );
        $this->assertSame(sprintf('NG-%d-0002', $year), $second->invoice_number);
    }

    public function test_invoice_number_resets_each_calendar_year(): void
    {
        $generator = app(InvoiceNumberGenerator::class);

        $this->assertSame('NG-2026-0001', $generator->next(2026));
        $this->assertSame('NG-2026-0002', $generator->next(2026));
        $this->assertSame('NG-2027-0001', $generator->next(2027));
    }

    public function test_double_issue_is_idempotent(): void
    {
        $draft = app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin);
        $first = app(IssueInvoice::class)->handle($draft);
        $second = app(IssueInvoice::class)->handle($first->fresh());

        $this->assertSame($first->invoice_number, $second->invoice_number);
        $this->assertDatabaseCount('invoices', 1);
        $this->assertSame(1, (int) DB::table('invoice_number_counters')
            ->where('year', now()->year)
            ->value('last_number'));
    }

    public function test_void_retains_number_and_does_not_reuse_it(): void
    {
        $issued = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin),
        );
        $number = $issued->invoice_number;

        app(VoidInvoice::class)->handle($issued, 'Wrong address');

        $issued->refresh();
        $this->assertSame(InvoiceStatus::Void, $issued->status);
        $this->assertSame($number, $issued->invoice_number);
        $this->assertNotNull($issued->voided_at);
        $this->assertTrue(Storage::disk('local')->exists($issued->pdf_path));

        $next = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin),
        );
        $this->assertSame(sprintf('NG-%d-0002', now()->year), $next->invoice_number);
    }

    public function test_issued_pdf_download_requires_auth_and_uses_invoice_filename(): void
    {
        $issued = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin),
        );

        $this->get(route('filament.admin.resources.invoices.view', ['record' => $issued]))
            ->assertRedirect();

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.invoices.view', ['record' => $issued]))
            ->assertOk()
            ->assertSee($issued->invoice_number, false);

        $response = app(InvoicePdfService::class)->streamStoredPdf($issued);
        $disposition = (string) $response->headers->get('Content-Disposition');
        $this->assertStringContainsString($issued->invoice_number.'.pdf', $disposition);
    }

    public function test_missing_stored_pdf_does_not_silently_regenerate(): void
    {
        $issued = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin),
        );

        Storage::disk('local')->delete($issued->pdf_path);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('will not be regenerated automatically');

        app(InvoicePdfService::class)->storedPdfContents($issued->fresh());
    }

    public function test_issued_snapshot_immutable_after_booking_customer_and_settings_change(): void
    {
        $booking = $this->makeBooking(['agreed_price_pence' => 22500]);
        $invoice = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($booking, $this->admin),
        );

        $originalNumber = $invoice->invoice_number;
        $originalTotal = $invoice->total_pence;
        $originalCustomer = $invoice->customer_name;
        $originalBusinessPhone = $invoice->business_phone;
        $originalPdf = Storage::disk('local')->get($invoice->pdf_path);

        $booking->update(['agreed_price_pence' => 30000]);
        $this->customer->update([
            'first_name' => 'Changed',
            'last_name' => 'Name',
            'address_line1' => '99 New Street',
        ]);
        SiteSetting::instance()->update([
            'phone' => '01159999999',
            'business_name' => 'Changed Business Ltd',
        ]);

        $invoice->refresh()->load('items');

        $this->assertSame($originalNumber, $invoice->invoice_number);
        $this->assertSame($originalTotal, $invoice->total_pence);
        $this->assertSame($originalCustomer, $invoice->customer_name);
        $this->assertSame($originalBusinessPhone, $invoice->business_phone);
        $this->assertSame(22500, (int) $invoice->items->first()->line_total_pence);
        $this->assertSame($originalPdf, Storage::disk('local')->get($invoice->pdf_path));
    }

    public function test_money_totals_with_quantity_discount_and_vat(): void
    {
        $invoice = app(CreateInvoiceFromBooking::class)->handle(
            $this->makeBooking(['agreed_price_pence' => 10000]),
            $this->admin,
        );

        $invoice->items()->delete();
        InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Deep Cleaning',
            'quantity' => 2,
            'unit_price_pence' => 10000,
            'line_total_pence' => 20000,
            'sort_order' => 1,
        ]);
        InvoiceItem::query()->create([
            'invoice_id' => $invoice->id,
            'description' => 'Oven interior',
            'quantity' => 1,
            'unit_price_pence' => 4500,
            'line_total_pence' => 4500,
            'sort_order' => 2,
        ]);

        $invoice->forceFill([
            'discount_pence' => 2500,
            'vat_registered' => true,
            'vat_rate_percent' => '20.00',
        ])->save();

        app(RecalculateInvoiceTotals::class)->handle($invoice->fresh(['items']));
        $invoice->refresh();

        $this->assertSame(24500, $invoice->subtotal_pence);
        $this->assertSame(2500, $invoice->discount_pence);
        $this->assertSame(4400, $invoice->vat_pence);
        $this->assertSame(26400, $invoice->total_pence);
    }

    public function test_payments_drive_paid_outstanding_and_status(): void
    {
        $booking = $this->makeBooking(['agreed_price_pence' => 22500]);
        $invoice = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($booking, $this->admin),
        );

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 5000,
            'type' => PaymentType::Deposit,
            'method' => PaymentMethod::Card,
            'paid_date' => now()->toDateString(),
        ]);

        $invoice->refresh();
        $this->assertSame(5000, $invoice->paidPence());
        $this->assertSame(17500, $invoice->outstandingPence());
        $this->assertSame(InvoiceStatus::Issued, $invoice->status);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 17500,
            'type' => PaymentType::Balance,
            'method' => PaymentMethod::BankTransfer,
            'paid_date' => now()->toDateString(),
        ]);

        $invoice->refresh();
        $this->assertSame(22500, $invoice->paidPence());
        $this->assertSame(0, $invoice->outstandingPence());
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertNotNull($invoice->paid_at);

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 5000,
            'type' => PaymentType::Refund,
            'method' => PaymentMethod::BankTransfer,
            'paid_date' => now()->toDateString(),
        ]);

        $invoice->refresh();
        $this->assertSame(17500, $invoice->paidPence());
        $this->assertSame(5000, $invoice->outstandingPence());
        $this->assertSame(InvoiceStatus::Issued, $invoice->status);
        $this->assertNull($invoice->paid_at);
    }

    public function test_send_email_queues_delivery_attaches_stored_pdf_and_marks_sent(): void
    {
        Mail::fake();

        $invoice = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin),
        );
        $pdfBefore = Storage::disk('local')->get($invoice->pdf_path);

        $delivery = app(QueueInvoiceEmail::class)->handle(
            $invoice,
            'sam.brooks@example.com',
            $this->admin,
            'Invoice test subject',
            "Hi Sam,\n\nPlease find your invoice attached.",
        );

        $this->assertSame(InvoiceDeliveryStatus::Queued, $delivery->status);

        (new SendInvoiceMailJob($delivery->id, 'Invoice test subject', "Hi Sam,\n\nPlease find your invoice attached."))
            ->handle(app(InvoicePdfService::class));

        Mail::assertSent(CustomerInvoiceMail::class, function (CustomerInvoiceMail $mail) use ($invoice, $pdfBefore): bool {
            $this->assertSame('Invoice test subject', $mail->envelope()->subject);
            $this->assertSame($invoice->id, $mail->invoice->id);
            $this->assertSame($pdfBefore, $mail->pdfContents);
            $this->assertNotEmpty($mail->attachments());

            return $mail->hasTo('sam.brooks@example.com');
        });

        $delivery->refresh();
        $invoice->refresh();
        $this->assertSame(InvoiceDeliveryStatus::Sent, $delivery->status);
        $this->assertSame(InvoiceStatus::Sent, $invoice->status);
        $this->assertNotNull($invoice->first_sent_at);
        $this->assertNotNull($invoice->last_sent_at);
    }

    public function test_resend_keeps_paid_status(): void
    {
        Mail::fake();

        $booking = $this->makeBooking(['agreed_price_pence' => 10000]);
        $invoice = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($booking, $this->admin),
        );

        Payment::query()->create([
            'booking_id' => $booking->id,
            'amount_pence' => 10000,
            'type' => PaymentType::Full,
            'method' => PaymentMethod::Cash,
            'paid_date' => now()->toDateString(),
        ]);
        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);

        $delivery = app(QueueInvoiceEmail::class)->handle($invoice, 'sam.brooks@example.com', $this->admin);
        (new SendInvoiceMailJob($delivery->id))->handle(app(InvoicePdfService::class));

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Paid, $invoice->status);
        $this->assertNotNull($invoice->last_sent_at);
    }

    public function test_invalid_email_blocked_and_failure_preserves_invoice(): void
    {
        $invoice = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(), $this->admin),
        );

        try {
            app(QueueInvoiceEmail::class)->handle($invoice, 'not-an-email', $this->admin);
            $this->fail('Expected invalid email to be rejected.');
        } catch (\InvalidArgumentException $exception) {
            $this->assertStringContainsString('valid recipient email', $exception->getMessage());
        }

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Issued, $invoice->status);
        $this->assertDatabaseCount('invoice_deliveries', 0);
    }

    public function test_create_invoice_returns_existing_draft_and_blocks_duplicate_issued(): void
    {
        $booking = $this->makeBooking();
        $first = app(CreateInvoiceFromBooking::class)->handle($booking, $this->admin);
        $again = app(CreateInvoiceFromBooking::class)->handle($booking, $this->admin);
        $this->assertSame($first->id, $again->id);

        app(IssueInvoice::class)->handle($first);

        $this->expectException(\InvalidArgumentException::class);
        app(CreateInvoiceFromBooking::class)->handle($booking->fresh(), $this->admin);
    }

    public function test_invoice_list_and_booking_pages_show_invoice_actions(): void
    {
        $booking = $this->makeBooking();

        $this->actingAs($this->admin)
            ->get('/admin/invoices')
            ->assertOk()
            ->assertSee('No invoices yet', false);

        $this->actingAs($this->admin)
            ->get(route('filament.admin.resources.bookings.view', ['record' => $booking]))
            ->assertOk()
            ->assertSee('Create invoice', false);

        $invoice = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($booking, $this->admin),
        );

        $this->actingAs($this->admin)
            ->get('/admin/invoices')
            ->assertOk()
            ->assertSee($invoice->invoice_number, false);
    }

    public function test_overdue_is_derived_not_stored(): void
    {
        $invoice = app(IssueInvoice::class)->handle(
            app(CreateInvoiceFromBooking::class)->handle($this->makeBooking(['agreed_price_pence' => 10000]), $this->admin),
        );

        $invoice->forceFill([
            'due_date' => now()->subDays(3)->toDateString(),
        ])->save();

        $this->assertTrue($invoice->fresh()->isOverdue());
        $this->assertSame(InvoiceStatus::Issued, $invoice->fresh()->status);
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
}
