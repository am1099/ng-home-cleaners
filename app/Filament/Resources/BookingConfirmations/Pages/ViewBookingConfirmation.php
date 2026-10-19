<?php

namespace App\Filament\Resources\BookingConfirmations\Pages;

use App\Filament\Resources\BookingConfirmations\BookingConfirmationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBookingConfirmation extends ViewRecord
{
    protected static string $resource = BookingConfirmationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
