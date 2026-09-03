<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\InvoiceItem;

final class RecalculateInvoiceTotals
{
    public function handle(Invoice $invoice): Invoice
    {
        $items = $invoice->relationLoaded('items')
            ? $invoice->items
            : $invoice->items()->get();

        foreach ($items as $item) {
            /** @var InvoiceItem $item */
            $lineTotal = InvoiceItem::calculateLineTotalPence(
                $item->quantity,
                (int) $item->unit_price_pence,
            );

            if ((int) $item->line_total_pence !== $lineTotal) {
                $item->forceFill(['line_total_pence' => $lineTotal])->save();
            }
        }

        $invoice->unsetRelation('items');
        $invoice->load('items');
        $invoice->recalculateTotals();

        return $invoice->fresh(['items']) ?? $invoice;
    }
}
