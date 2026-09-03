<?php

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServices extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = ServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
