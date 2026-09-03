<?php

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListQuoteRequests extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add phone / manual lead')
                ->icon(Heroicon::OutlinedPhone),
        ];
    }
}
