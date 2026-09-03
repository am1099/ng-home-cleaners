<?php

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\Faqs\FaqResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFaqs extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
