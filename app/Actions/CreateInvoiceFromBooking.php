<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CreateInvoiceFromBooking
{
    public function __construct(
        private readonly RecalculateInvoiceTotals $recalculateInvoiceTotals,
    ) {}

    public function handle(Booking $booking, ?User $createdBy = null): Invoice
    {
        $booking->loadMissing(['customer', 'service', 'invoices']);

        $customer = $booking->customer;

        if (! $customer) {
            throw new InvalidArgumentException('A booking must have a customer before an invoice can be created.');
        }

        $existing = $booking->invoices
            ->first(fn (Invoice $invoice): bool => ! $invoice->isVoid());

        if ($existing) {
            if ($existing->isDraft()) {
                return $existing->loadMissing(['items', 'booking', 'customer']);
            }

            throw new InvalidArgumentException(
                'Booking '.$booking->reference.' already has invoice '.$existing->displayNumber().'.',
            );
        }

        $settings = SiteSetting::instance();
        $dueDays = (int) ($settings->invoice_due_days ?? 7);
        $agreedPence = (int) $booking->agreed_price_pence;
        $serviceName = $booking->service?->name ?: 'Cleaning service';

        return DB::transaction(function () use ($booking, $customer, $settings, $dueDays, $agreedPence, $serviceName, $createdBy): Invoice {
            $lockedExisting = Invoice::query()
                ->where('booking_id', $booking->id)
                ->where('status', '!=', InvoiceStatus::Void->value)
                ->lockForUpdate()
                ->first();

            if ($lockedExisting) {
                if ($lockedExisting->isDraft()) {
                    return $lockedExisting->loadMissing(['items', 'booking', 'customer']);
                }

                throw new InvalidArgumentException(
                    'Booking '.$booking->reference.' already has invoice '.$lockedExisting->displayNumber().'.',
                );
            }

            $invoice = Invoice::query()->create([
                'booking_id' => $booking->id,
                'customer_id' => $customer->id,
                'invoice_number' => null,
                'status' => InvoiceStatus::Draft,
                'issue_date' => null,
                'due_date' => now()->startOfDay()->addDays(max(0, $dueDays))->toDateString(),
                'currency' => 'GBP',
                'customer_name' => $customer->fullName(),
                'customer_email' => $customer->email,
                'customer_phone' => $customer->phone_display ?: $customer->phone_normalized,
                'billing_address_line1' => $booking->address_line1 ?: $customer->address_line1,
                'billing_address_line2' => $booking->address_line2 ?: $customer->address_line2,
                'billing_city' => $booking->city ?: $customer->city,
                'billing_postcode' => $booking->postcode ?: $customer->postcode,
                'business_name' => $settings->business_name ?? config('app.name'),
                'business_email' => $settings->email,
                'business_phone' => $settings->phoneDisplay(),
                'business_address' => $settings->business_address,
                'company_legal_name' => $settings->company_legal_name,
                'company_registration_number' => $settings->company_registration_number,
                'vat_registered' => (bool) $settings->vat_registered,
                'vat_number' => $settings->vat_registered ? $settings->vat_number : null,
                'vat_rate_percent' => $settings->vat_registered ? $settings->default_vat_rate_percent : null,
                'subtotal_pence' => $agreedPence,
                'discount_pence' => 0,
                'vat_pence' => 0,
                'total_pence' => $agreedPence,
                'notes' => $settings->invoice_default_notes,
                'payment_terms' => $settings->invoice_payment_terms
                    ?: ($dueDays === 0 ? 'Due on receipt' : "Payment due within {$dueDays} days"),
                'payment_instructions' => $settings->invoice_payment_instructions,
                'booking_reference' => $booking->reference,
                'booking_date' => $booking->booking_date,
                'service_name' => $serviceName,
                'created_by' => $createdBy?->id,
            ]);

            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'description' => $serviceName,
                'quantity' => 1,
                'unit_price_pence' => $agreedPence,
                'line_total_pence' => $agreedPence,
                'sort_order' => 1,
            ]);

            $invoice->load('items');
            $this->recalculateInvoiceTotals->handle($invoice);

            return $invoice->fresh(['items', 'booking', 'customer']) ?? $invoice;
        });
    }
}
