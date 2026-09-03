<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Actions\RecalculateInvoiceTotals;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Support\InvoiceActions;
use App\Models\Invoice;
use Filament\Resources\Pages\EditRecord;

class EditInvoice extends EditRecord
{
    protected static string $resource = InvoiceResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var Invoice $invoice */
        $invoice = $this->getRecord();

        if (! $invoice->isDraft()) {
            $this->redirect(InvoiceResource::getUrl('view', ['record' => $invoice]), navigate: true);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            InvoiceActions::preview(),
            InvoiceActions::issue(),
            InvoiceActions::deleteDraft(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Invoice $invoice */
        $invoice = $this->getRecord();

        app(RecalculateInvoiceTotals::class)->handle($invoice->fresh(['items']) ?? $invoice);

        $this->refreshFormData([
            'subtotal_pence',
            'discount_pence',
            'vat_pence',
            'total_pence',
        ]);
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }
}
