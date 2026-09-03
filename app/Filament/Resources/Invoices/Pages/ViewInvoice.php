<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Support\InvoiceActions;
use App\Models\Invoice;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        /** @var Invoice $record */
        $record = $this->getRecord();

        return match (true) {
            $record->isDraft() => [
                EditAction::make()->label('Edit'),
                InvoiceActions::preview(),
                InvoiceActions::issue(),
            ],
            $record->isVoid() => array_values(array_filter([
                filled($record->pdf_path) ? InvoiceActions::download() : null,
            ])),
            in_array($record->status, [
                InvoiceStatus::Issued,
                InvoiceStatus::Sent,
                InvoiceStatus::Paid,
            ], true) => [
                InvoiceActions::download()->button(),
                InvoiceActions::send()->button(),
                InvoiceActions::preview()->label('Preview PDF')->button()->color('gray'),
                InvoiceActions::void()->button(),
            ],
            default => [],
        };
    }
}
