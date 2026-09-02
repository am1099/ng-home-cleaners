<?php

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Actions\DispatchQuoteRequestNotifications;
use App\Actions\SendCustomerFinalQuote;
use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

class ViewQuoteRequest extends ViewRecord
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('resendLeadEmails')
                ->label('Resend lead emails')
                ->color('gray')
                ->icon(Heroicon::OutlinedEnvelope)
                ->requiresConfirmation()
                ->modalHeading('Resend new lead emails')
                ->modalDescription('Queues the internal team notification again. If the lead has an email address, the customer acknowledgement is sent again too.')
                ->action(function (): void {
                    app(DispatchQuoteRequestNotifications::class)->handle($this->getRecord()->fresh());

                    Notification::make()
                        ->title('Lead emails queued')
                        ->success()
                        ->send();
                }),
            Action::make('sendFinalQuoteEmail')
                ->label('Send final quote email')
                ->color('primary')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->visible(fn (): bool => $this->getRecord()->final_quote_amount_pence !== null
                    && filled($this->getRecord()->email))
                ->requiresConfirmation()
                ->modalHeading('Send final quote email')
                ->modalDescription('Emails the customer their confirmed quote amount. The lead is marked as quote sent if it is still new or contacted.')
                ->action(function (): void {
                    try {
                        app(SendCustomerFinalQuote::class)->handle($this->getRecord()->fresh());
                    } catch (ValidationException $exception) {
                        Notification::make()
                            ->title('Could not send quote email')
                            ->body(collect($exception->errors())->flatten()->first() ?? 'Please check the lead details and try again.')
                            ->danger()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Final quote email queued')
                        ->success()
                        ->send();
                }),
            Action::make('convertToBooking')
                ->label('Convert to Booking')
                ->color('success')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->visible(fn (): bool => $this->getRecord()->status === QuoteRequestStatus::Won
                    && ! $this->getRecord()->bookings()->exists())
                ->url(fn (): string => BookingResource::getUrl('create', [
                    'lead' => $this->getRecord()->getKey(),
                ])),
            Action::make('viewBooking')
                ->label('View booking')
                ->color('gray')
                ->icon(Heroicon::OutlinedEye)
                ->visible(fn (): bool => $this->getRecord()->bookings()->exists())
                ->url(fn (): string => BookingResource::getUrl('view', [
                    'record' => $this->getRecord()->bookings()->latest('id')->first(),
                ])),
            Action::make('markContacted')
                ->label('Mark contacted')
                ->color('warning')
                ->icon(Heroicon::OutlinedPhone)
                ->visible(fn (): bool => $this->getRecord()->status === QuoteRequestStatus::New)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->markStatus(QuoteRequestStatus::Contacted);
                    Notification::make()->title('Lead marked as contacted')->success()->send();
                }),
            Action::make('markQuoteSent')
                ->label('Mark quote sent')
                ->color('primary')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->visible(fn (): bool => in_array($this->getRecord()->status, [QuoteRequestStatus::New, QuoteRequestStatus::Contacted], true))
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->markStatus(QuoteRequestStatus::QuoteSent);
                    Notification::make()->title('Lead marked as quote sent')->success()->send();
                }),
            Action::make('markWon')
                ->label('Mark won')
                ->color('success')
                ->icon(Heroicon::OutlinedTrophy)
                ->visible(fn (): bool => $this->getRecord()->status !== QuoteRequestStatus::Won)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->markStatus(QuoteRequestStatus::Won);
                    Notification::make()->title('Lead marked as won')->success()->send();
                }),
            Action::make('markLost')
                ->label('Mark lost')
                ->color('danger')
                ->icon(Heroicon::OutlinedXCircle)
                ->visible(fn (): bool => $this->getRecord()->status !== QuoteRequestStatus::Lost)
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->markStatus(QuoteRequestStatus::Lost);
                    Notification::make()->title('Lead marked as lost')->success()->send();
                }),
            EditAction::make()
                ->label('Update quote & notes')
                ->icon(Heroicon::OutlinedDocumentText),
        ];
    }
}
