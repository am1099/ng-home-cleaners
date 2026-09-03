<?php

namespace App\Filament\Resources\GalleryItems\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\GalleryItems\GalleryItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGalleryItems extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = GalleryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
