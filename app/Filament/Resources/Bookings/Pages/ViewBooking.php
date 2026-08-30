<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markCompleted')
                ->label('Mark completed')
                ->color('success')
                ->icon(Heroicon::OutlinedCheckCircle)
                ->visible(fn (): bool => $this->getRecord()->status === BookingStatus::Scheduled)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->markStatus(BookingStatus::Completed);
                    Notification::make()->title('Booking marked completed')->success()->send();
                }),
            Action::make('markCancelled')
                ->label('Cancel booking')
                ->color('danger')
                ->icon(Heroicon::OutlinedXCircle)
                ->visible(fn (): bool => $this->getRecord()->status !== BookingStatus::Cancelled)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->markStatus(BookingStatus::Cancelled);
                    Notification::make()->title('Booking cancelled')->success()->send();
                }),
            EditAction::make(),
        ];
    }
}
