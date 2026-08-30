<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Services\BookingClashDetector;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditBooking extends EditRecord
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
                    $this->refreshFormData(['status', 'completed_at']);
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
                    $this->refreshFormData(['status', 'cancelled_at']);
                }),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        $warning = app(BookingClashDetector::class)->warningMessage(
            $record->booking_date,
            $record->arrival_window,
            $record->id,
        );

        if ($warning) {
            Notification::make()
                ->title('Booking saved with clash warning')
                ->body($warning)
                ->warning()
                ->persistent()
                ->send();
        }
    }
}
