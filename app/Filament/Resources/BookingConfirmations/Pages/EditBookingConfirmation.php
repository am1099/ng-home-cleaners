<?php

namespace App\Filament\Resources\BookingConfirmations\Pages;

use App\Enums\BookingConfirmationStatus;
use App\Filament\Resources\BookingConfirmations\BookingConfirmationResource;
use App\Models\BookingConfirmation;
use Filament\Resources\Pages\EditRecord;

class EditBookingConfirmation extends EditRecord
{
    protected static string $resource = BookingConfirmationResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        /** @var BookingConfirmation $record */
        $record = $this->getRecord();

        if (($data['status'] ?? null) === BookingConfirmationStatus::Reviewed->value && $record->reviewed_at === null) {
            $data['reviewed_at'] = now();
        }

        if (($data['status'] ?? null) === BookingConfirmationStatus::New->value) {
            $data['reviewed_at'] = null;
        }

        return $data;
    }
}
