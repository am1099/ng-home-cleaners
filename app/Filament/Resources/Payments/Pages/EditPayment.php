<?php

namespace App\Filament\Resources\Payments\Pages;

use App\Enums\PaymentType;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Models\Booking;
use App\Pricing\Money;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPayment extends EditRecord
{
    protected static string $resource = PaymentResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['type'] ?? null) === PaymentType::Refund->value && isset($data['amount_pence'])) {
            $data['amount_pence'] = abs((int) $data['amount_pence']);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $type = PaymentType::from($data['type']);
        $data['amount_pence'] = Booking::normalizePaymentAmountPence($type, (int) $data['amount_pence']);

        return $data;
    }

    protected function afterSave(): void
    {
        $booking = $this->getRecord()->booking?->fresh();

        if ($booking?->isOverpaid()) {
            Notification::make()
                ->title('Overpayment warning')
                ->body('Total paid ('.$booking->paidDisplay().') exceeds the agreed price ('.$booking->agreedDisplay().') by '.Money::formatPence($booking->overpaidPence()).'.')
                ->warning()
                ->persistent()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return BookingResource::getUrl('view', ['record' => $this->getRecord()->booking_id]);
    }
}
