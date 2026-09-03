<?php

namespace App\Filament\Resources\Bookings\Pages;

use App\Actions\CreateInvoiceFromBooking;
use App\Actions\SendReviewRequest;
use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Booking;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('invoice')
                ->label(fn (): string => $this->invoiceActionLabel())
                ->icon(Heroicon::OutlinedDocumentText)
                ->color('primary')
                ->visible(fn (): bool => $this->invoiceActionVisible())
                ->action(function (): void {
                    /** @var Booking $booking */
                    $booking = $this->getRecord()->loadMissing('invoices');
                    $active = $booking->activeInvoice();

                    if ($active?->isDraft()) {
                        $this->redirect(InvoiceResource::getUrl('edit', ['record' => $active]), navigate: true);

                        return;
                    }

                    if ($active) {
                        $this->redirect(InvoiceResource::getUrl('view', ['record' => $active]), navigate: true);

                        return;
                    }

                    try {
                        $invoice = app(CreateInvoiceFromBooking::class)->handle($booking, Auth::user());
                    } catch (InvalidArgumentException $exception) {
                        Notification::make()
                            ->title('Could not create invoice')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Draft invoice created')
                        ->success()
                        ->send();

                    $this->redirect(InvoiceResource::getUrl('edit', ['record' => $invoice]), navigate: true);
                }),
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
            Action::make('requestReview')
                ->label('Request Google review')
                ->icon(Heroicon::OutlinedStar)
                ->visible(fn (): bool => $this->getRecord()->status === BookingStatus::Completed)
                ->requiresConfirmation()
                ->action(function (): void {
                    $booking = $this->getRecord();
                    $booking->forceFill(['review_request_sent_at' => null])->save();
                    app(SendReviewRequest::class)->handle($booking);
                    Notification::make()->title('Review request queued')->success()->send();
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

    private function invoiceActionVisible(): bool
    {
        /** @var Booking $booking */
        $booking = $this->getRecord()->loadMissing('invoices');

        if ($booking->status === BookingStatus::Cancelled && ! $booking->activeInvoice()) {
            return false;
        }

        return $booking->customer_id !== null
            && ($booking->canCreateInvoice() || $booking->activeInvoice() !== null);
    }

    private function invoiceActionLabel(): string
    {
        /** @var Booking $booking */
        $booking = $this->getRecord()->loadMissing('invoices');
        $active = $booking->activeInvoice();

        if ($active?->isDraft()) {
            return 'Continue invoice';
        }

        if ($active) {
            return 'View invoice';
        }

        return 'Create invoice';
    }
}
