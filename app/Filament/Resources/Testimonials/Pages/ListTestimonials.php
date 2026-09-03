<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\Testimonials\TestimonialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTestimonials extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
