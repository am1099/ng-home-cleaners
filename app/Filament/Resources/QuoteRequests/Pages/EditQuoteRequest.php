<?php

namespace App\Filament\Resources\QuoteRequests\Pages;

use App\Actions\SendCustomerFinalQuote;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditQuoteRequest extends EditRecord
{
    protected static string $resource = QuoteRequestResource::class;

    protected static ?string $title = 'Update lead';

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function afterSave(): void
    {
        $record = $this->getRecord();

        if (! $record->wasChanged('final_quote_amount_pence')
            || $record->final_quote_amount_pence === null
            || blank($record->email)) {
            return;
        }

        try {
            app(SendCustomerFinalQuote::class)->handle($record->fresh());
        } catch (ValidationException $exception) {
            Notification::make()
                ->title('Lead saved, but quote email was not sent')
                ->body(collect($exception->errors())->flatten()->first() ?? 'Check the email address and try again from the lead page.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Final quote email sent')
            ->body('The customer has been emailed their confirmed quote.')
            ->success()
            ->send();
    }
}
