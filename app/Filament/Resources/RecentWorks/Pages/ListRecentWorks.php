<?php

namespace App\Filament\Resources\RecentWorks\Pages;

use App\Filament\Resources\RecentWorks\RecentWorkResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRecentWorks extends ListRecords
{
    protected static string $resource = RecentWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
