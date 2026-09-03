<?php

namespace App\Actions;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use InvalidArgumentException;

final class VoidInvoice
{
    public function handle(Invoice $invoice, ?string $reason = null): Invoice
    {
        if ($invoice->isVoid()) {
            return $invoice;
        }

        if ($invoice->isDraft()) {
            throw new InvalidArgumentException('Draft invoices should be deleted rather than voided.');
        }

        $invoice->forceFill([
            'status' => InvoiceStatus::Void,
            'voided_at' => $invoice->voided_at ?? now(),
            'void_reason' => $reason,
            'paid_at' => null,
        ])->save();

        return $invoice->fresh() ?? $invoice;
    }
}
