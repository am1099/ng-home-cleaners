<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Filament\Resources\Invoices\Widgets\InvoiceStatsOverview;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Create invoice'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            InvoiceStatsOverview::class,
        ];
    }
}
