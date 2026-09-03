<?php

namespace App\Services;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;

final class InvoiceBalanceService
{
    public function syncPaidStatus(Invoice $invoice): Invoice
    {
        if ($invoice->isVoid() || $invoice->isDraft()) {
            return $invoice;
        }

        $outstanding = $invoice->amountDuePence();

        if ($outstanding <= 0) {
            if ($invoice->status !== InvoiceStatus::Paid || blank($invoice->paid_at)) {
                $invoice->forceFill([
                    'status' => InvoiceStatus::Paid,
                    'paid_at' => $invoice->paid_at ?? now(),
                ])->save();
            }

            return $invoice->fresh() ?? $invoice;
        }

        if ($invoice->status === InvoiceStatus::Paid) {
            $invoice->forceFill([
                'status' => filled($invoice->first_sent_at) ? InvoiceStatus::Sent : InvoiceStatus::Issued,
                'paid_at' => null,
            ])->save();
        }

        return $invoice->fresh() ?? $invoice;
    }

    public function syncForBookingId(int $bookingId): void
    {
        Invoice::query()
            ->where('booking_id', $bookingId)
            ->whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::Sent->value,
                InvoiceStatus::Paid->value,
            ])
            ->get()
            ->each(fn (Invoice $invoice) => $this->syncPaidStatus($invoice));
    }
}
