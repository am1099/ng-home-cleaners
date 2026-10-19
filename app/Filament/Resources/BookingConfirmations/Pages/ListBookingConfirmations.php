<?php

namespace App\Filament\Resources\BookingConfirmations\Pages;

use App\Filament\Resources\BookingConfirmations\BookingConfirmationResource;
use Filament\Resources\Pages\ListRecords;

class ListBookingConfirmations extends ListRecords
{
    protected static string $resource = BookingConfirmationResource::class;
}
