<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Concerns\HasToggleableRecordLayout;
use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBookings extends ListRecords
{
    use HasToggleableRecordLayout;

    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
