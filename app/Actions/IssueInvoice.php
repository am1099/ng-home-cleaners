<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Models\SiteSetting;
use App\Services\InvoiceNumberGenerator;
use App\Services\InvoicePdfService;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class IssueInvoice
{
    public function __construct(
        private readonly InvoiceNumberGenerator $invoiceNumberGenerator,
        private readonly RecalculateInvoiceTotals $recalculateInvoiceTotals,
        private readonly InvoicePdfService $invoicePdfService,
    ) {}

    public function handle(Invoice $invoice): Invoice
    {
        if ($invoice->isIssuedOrLater() || filled($invoice->invoice_number)) {
            return $invoice->fresh(['items', 'booking', 'customer']) ?? $invoice;
        }

        if ($invoice->isVoid()) {
            throw new InvalidArgumentException('A void invoice cannot be issued.');
        }

        if (! $invoice->isDraft()) {
            throw new InvalidArgumentException('Only draft invoices can be issued.');
        }

        return DB::transaction(function () use ($invoice): Invoice {
            /** @var Invoice $invoice */
            $invoice = Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->with(['items', 'booking.customer', 'customer'])
                ->firstOrFail();

            if ($invoice->isIssuedOrLater() || filled($invoice->invoice_number)) {
                return $invoice;
            }

            if ($invoice->items->isEmpty()) {
                throw new InvalidArgumentException('An invoice must have at least one line item before it can be issued.');
            }

            if (blank($invoice->customer_name)) {
                throw new InvalidArgumentException('An invoice must have a customer name before it can be issued.');
            }

            $settings = SiteSetting::instance();
            $this->recalculateInvoiceTotals->handle($invoice);
            $invoice->refresh()->load('items');

            $number = $this->invoiceNumberGenerator->next(
                $invoice->issue_date?->year ?? (int) now()->year,
            );

            $invoice->forceFill([
                'invoice_number' => $number,
                'status' => InvoiceStatus::Issued,
                'issue_date' => $invoice->issue_date?->toDateString() ?? now()->toDateString(),
                'due_date' => $invoice->due_date?->toDateString()
                    ?? now()->startOfDay()->addDays((int) ($settings->invoice_due_days ?? 7))->toDateString(),
                'business_name' => $invoice->business_name ?: ($settings->business_name ?? config('app.name')),
                'business_email' => $invoice->business_email ?: $settings->email,
                'business_phone' => $invoice->business_phone ?: $settings->phoneDisplay(),
                'business_address' => $invoice->business_address ?: $settings->business_address,
                'company_legal_name' => $invoice->company_legal_name ?: $settings->company_legal_name,
                'company_registration_number' => $invoice->company_registration_number ?: $settings->company_registration_number,
                'vat_registered' => (bool) $invoice->vat_registered,
                'vat_number' => $invoice->vat_registered ? ($invoice->vat_number ?: $settings->vat_number) : null,
                'vat_rate_percent' => $invoice->vat_registered
                    ? ($invoice->vat_rate_percent ?: $settings->default_vat_rate_percent)
                    : null,
                'issued_at' => now(),
            ])->save();

            $this->recalculateInvoiceTotals->handle($invoice);
            $invoice->refresh()->load(['items', 'booking', 'customer']);

            $this->invoicePdfService->generateAndStoreFinalPdf($invoice);

            return $invoice->fresh(['items', 'booking', 'customer']) ?? $invoice;
        });
    }
}
