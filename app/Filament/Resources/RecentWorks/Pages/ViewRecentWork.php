<?php

namespace App\Filament\Resources\RecentWorks\Pages;

use App\Filament\Resources\RecentWorks\RecentWorkResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRecentWork extends ViewRecord
{
    protected static string $resource = RecentWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
