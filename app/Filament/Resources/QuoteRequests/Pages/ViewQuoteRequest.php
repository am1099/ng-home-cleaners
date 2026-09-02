<?php

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Actions\DispatchQuoteRequestNotifications;
use App\Actions\SendCustomerFinalQuote;
use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\ValidationException;

class ViewQuoteRequest extends ViewRecord
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Update quote')
                ->icon(Heroicon::OutlinedDocumentText),

            ActionGroup::make([
                ActionGroup::make([
                    Action::make('resendLeadEmails')
                        ->label('Resend lead emails')
                        ->icon(Heroicon::OutlinedEnvelope)
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Resend new lead emails')
                        ->modalDescription('Sends the internal team notification again. If the lead has an email address, the customer acknowledgement is sent again too.')
                        ->action(function (): void {
                            $result = app(DispatchQuoteRequestNotifications::class)->handle($this->getRecord()->fresh());

                            if ($result['failed'] === []) {
                                Notification::make()
                                    ->title('Lead emails sent')
                                    ->success()
                                    ->send();

                                return;
                            }

                            $failedAddresses = collect($result['failed'])->pluck('to')->join(', ');

                            Notification::make()
                                ->title($result['sent'] === [] ? 'Lead emails failed' : 'Some lead emails failed')
                                ->body('Could not send to: '.$failedAddresses.'. With Resend testing (onboarding@resend.dev), you can only send to the Gmail on your Resend account.')
                                ->danger()
                                ->send();
                        }),
                    Action::make('sendFinalQuoteEmail')
                        ->label('Send final quote email')
                        ->icon(Heroicon::OutlinedPaperAirplane)
                        ->color('primary')
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
                                ->title('Final quote email sent')
                                ->success()
                                ->send();
                        }),
                ])
                    ->label('Emails')
                    ->icon(Heroicon::OutlinedEnvelope)
                    ->dropdown(false),

                ActionGroup::make([
                    Action::make('markContacted')
                        ->label('Mark contacted')
                        ->icon(Heroicon::OutlinedPhone)
                        ->color('warning')
                        ->visible(fn (): bool => $this->getRecord()->status === QuoteRequestStatus::New)
                        ->requiresConfirmation()
                        ->action(function (): void {
                            $this->getRecord()->markStatus(QuoteRequestStatus::Contacted);
                            Notification::make()->title('Lead marked as contacted')->success()->send();
                        }),
                    Action::make('markQuoteSent')
                        ->label('Mark quote sent')
                        ->icon(Heroicon::OutlinedCheckCircle)
                        ->color('primary')
                        ->visible(fn (): bool => in_array($this->getRecord()->status, [QuoteRequestStatus::New, QuoteRequestStatus::Contacted], true))
                        ->requiresConfirmation()
                        ->action(function (): void {
                            $this->getRecord()->markStatus(QuoteRequestStatus::QuoteSent);
                            Notification::make()->title('Lead marked as quote sent')->success()->send();
                        }),
                    Action::make('markWon')
                        ->label('Mark won')
                        ->icon(Heroicon::OutlinedTrophy)
                        ->color('success')
                        ->visible(fn (): bool => $this->getRecord()->status !== QuoteRequestStatus::Won)
                        ->requiresConfirmation()
                        ->action(function (): void {
                            $this->getRecord()->markStatus(QuoteRequestStatus::Won);
                            Notification::make()->title('Lead marked as won')->success()->send();
                        }),
                    Action::make('markLost')
                        ->label('Mark lost')
                        ->icon(Heroicon::OutlinedXCircle)
                        ->color('danger')
                        ->visible(fn (): bool => $this->getRecord()->status !== QuoteRequestStatus::Lost)
                        ->requiresConfirmation()
                        ->action(function (): void {
                            $this->getRecord()->markStatus(QuoteRequestStatus::Lost);
                            Notification::make()->title('Lead marked as lost')->success()->send();
                        }),
                ])
                    ->label('Status')
                    ->icon(Heroicon::OutlinedFlag)
                    ->dropdown(false),

                Action::make('convertToBooking')
                    ->label('Convert to booking')
                    ->icon(Heroicon::OutlinedCalendarDays)
                    ->color('success')
                    ->visible(fn (): bool => $this->getRecord()->status === QuoteRequestStatus::Won
                        && ! $this->getRecord()->bookings()->exists())
                    ->url(fn (): string => BookingResource::getUrl('create', [
                        'lead' => $this->getRecord()->getKey(),
                    ])),
                Action::make('viewBooking')
                    ->label('View booking')
                    ->icon(Heroicon::OutlinedEye)
                    ->color('gray')
                    ->visible(fn (): bool => $this->getRecord()->bookings()->exists())
                    ->url(fn (): string => BookingResource::getUrl('view', [
                        'record' => $this->getRecord()->bookings()->latest('id')->first(),
                    ])),
            ])
                ->label('Actions')
                ->icon(Heroicon::OutlinedEllipsisVertical)
                ->button()
                ->color('gray')
                ->dropdownPlacement('bottom-end')
                ->dropdownFlip(false)
                ->dropdownTeleport()
                ->dropdownOffset(12)
                ->dropdownWidth(Width::ExtraSmall),
        ];
    }
}
