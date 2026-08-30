<?php

namespace App\Filament\Resources\RecentWorks\Pages;

use App\Filament\Resources\RecentWorks\RecentWorkResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRecentWork extends EditRecord
{
    protected static string $resource = RecentWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
