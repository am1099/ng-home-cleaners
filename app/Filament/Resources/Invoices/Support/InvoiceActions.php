<?php

namespace App\Filament\Resources\Invoices\Support;

use App\Actions\IssueInvoice;
use App\Actions\QueueInvoiceEmail;
use App\Actions\VoidInvoice;
use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Invoice;
use App\Models\SiteSetting;
use App\Services\InvoicePdfService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;
use Throwable;

final class InvoiceActions
{
    public static function editDraft(): EditAction
    {
        return EditAction::make()
            ->label('Edit')
            ->icon(Heroicon::OutlinedPencilSquare)
            ->visible(fn (Invoice $record): bool => $record->isDraft());
    }

    public static function preview(): Action
    {
        return Action::make('preview')
            ->label('Preview')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->visible(fn (Invoice $record): bool => $record->isDraft() || filled($record->pdf_path))
            ->action(function (Invoice $record) {
                $pdf = app(InvoicePdfService::class);

                try {
                    if ($record->isDraft()) {
                        return $pdf->streamDraftPreview($record);
                    }

                    return $pdf->streamStoredPdf($record, inline: true);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Could not open preview')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return null;
                }
            });
    }

    public static function download(): Action
    {
        return Action::make('download')
            ->label('Download PDF')
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->color('gray')
            ->visible(fn (Invoice $record): bool => filled($record->pdf_path)
                && ($record->isIssuedOrLater() || $record->isVoid()))
            ->action(function (Invoice $record) {
                try {
                    return app(InvoicePdfService::class)->streamStoredPdf($record);
                } catch (Throwable $exception) {
                    Notification::make()
                        ->title('Could not download PDF')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return null;
                }
            });
    }

    public static function issue(): Action
    {
        return Action::make('issue')
            ->label('Issue invoice')
            ->icon(Heroicon::OutlinedDocumentCheck)
            ->color('success')
            ->visible(fn (Invoice $record): bool => $record->isDraft())
            ->requiresConfirmation()
            ->modalHeading('Issue this invoice?')
            ->modalDescription('Once issued, invoice details and financial line items should no longer be freely edited. A final invoice number and PDF will be created.')
            ->modalSubmitActionLabel('Issue invoice')
            ->action(function (Invoice $record) {
                try {
                    $invoice = app(IssueInvoice::class)->handle($record->fresh(['items']) ?? $record);
                } catch (InvalidArgumentException $exception) {
                    Notification::make()
                        ->title('Could not issue invoice')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return null;
                }

                Notification::make()
                    ->title('Invoice '.$invoice->displayNumber().' issued')
                    ->success()
                    ->send();

                return redirect(InvoiceResource::getUrl('view', ['record' => $invoice]));
            });
    }

    public static function send(): Action
    {
        return Action::make('send')
            ->label(fn (Invoice $record): string => filled($record->first_sent_at) ? 'Resend invoice' : 'Send invoice')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('primary')
            ->visible(fn (Invoice $record): bool => in_array($record->status, [
                InvoiceStatus::Issued,
                InvoiceStatus::Sent,
                InvoiceStatus::Paid,
            ], true) && filled($record->pdf_path))
            ->fillForm(function (Invoice $record): array {
                $business = $record->business_name ?: (SiteSetting::instance()->business_name ?? config('app.name'));

                return [
                    'to' => $record->customer_email,
                    'subject' => 'Invoice '.$record->displayNumber().' from '.$business,
                    'message' => "Hello,\n\nPlease find your invoice attached.\n\nThank you,\n".$business,
                ];
            })
            ->form([
                TextInput::make('to')
                    ->label('To')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('subject')
                    ->required()
                    ->maxLength(255),
                Textarea::make('message')
                    ->rows(6)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->modalHeading('Send invoice')
            ->modalSubmitActionLabel('Queue email')
            ->action(function (Invoice $record, array $data): void {
                try {
                    app(QueueInvoiceEmail::class)->handle(
                        $record,
                        $data['to'] ?? null,
                        Auth::user(),
                        $data['subject'] ?? null,
                        $data['message'] ?? null,
                    );
                } catch (InvalidArgumentException $exception) {
                    Notification::make()
                        ->title('Could not send invoice')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Invoice '.$record->displayNumber().' has been queued for sending to '.($data['to'] ?? $record->customer_email))
                    ->success()
                    ->send();
            });
    }

    public static function void(): Action
    {
        return Action::make('void')
            ->label('Void invoice')
            ->icon(Heroicon::OutlinedNoSymbol)
            ->color('danger')
            ->visible(fn (Invoice $record): bool => $record->isIssuedOrLater())
            ->requiresConfirmation()
            ->modalHeading(fn (Invoice $record): string => 'Void invoice '.$record->displayNumber().'?')
            ->modalDescription('This keeps the invoice and its history but removes it from outstanding balances.')
            ->form([
                Textarea::make('void_reason')
                    ->label('Reason (optional)')
                    ->rows(3),
            ])
            ->modalSubmitActionLabel('Void invoice')
            ->action(function (Invoice $record, array $data): void {
                try {
                    app(VoidInvoice::class)->handle($record, $data['void_reason'] ?? null);
                } catch (InvalidArgumentException $exception) {
                    Notification::make()
                        ->title('Could not void invoice')
                        ->body($exception->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title('Invoice voided')
                    ->success()
                    ->send();
            });
    }

    public static function deleteDraft(): DeleteAction
    {
        return DeleteAction::make()
            ->label('Delete draft')
            ->visible(fn (Invoice $record): bool => $record->isDraft());
    }
}
