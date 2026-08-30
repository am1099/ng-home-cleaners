<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Filament\Concerns\RedirectsToViewOrIndexAfterCreate;
use App\Filament\Resources\Bookings\BookingResource;
use App\Models\QuoteRequest;
use App\Services\BookingClashDetector;
use App\Services\BookingConversionService;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    use RedirectsToViewOrIndexAfterCreate;

    protected static string $resource = BookingResource::class;

    public function mount(): void
    {
        parent::mount();

        $leadId = request()->integer('lead') ?: null;

        if (! $leadId) {
            return;
        }

        $lead = QuoteRequest::query()->find($leadId);

        if (! $lead) {
            return;
        }

        $this->form->fill(app(BookingConversionService::class)->prefillFromLead($lead));
    }

    protected function afterCreate(): void
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
