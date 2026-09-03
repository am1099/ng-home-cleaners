<?php

namespace App\Filament\Resources\ServiceAreas\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\ServiceAreas\ServiceAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceAreas extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = ServiceAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
