<?php

namespace App\Notifications;

use App\Models\BookingConfirmation;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Notifications\Notification;

class NewBookingConfirmationNotification extends Notification
{
    public function __construct(
        public BookingConfirmation $confirmation,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        return FilamentNotification::make()
            ->title('Booking confirmation from '.$this->confirmation->full_name)
            ->body($this->confirmation->address)
            ->icon('heroicon-o-check-badge')
            ->actions([
                Action::make('view')
                    ->label('View confirmation')
                    ->url(route('filament.admin.resources.booking-confirmations.view', ['record' => $this->confirmation])),
            ])
            ->getDatabaseMessage();
    }
}
