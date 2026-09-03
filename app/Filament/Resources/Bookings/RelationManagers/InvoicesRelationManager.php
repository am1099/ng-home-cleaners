<?php

namespace App\Filament\Resources\Bookings\RelationManagers;

use App\Actions\CreateInvoiceFromBooking;
use App\Enums\InvoiceStatus;
use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Booking;
use App\Models\Invoice;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use InvalidArgumentException;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $title = 'Invoices';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice number')
                    ->state(fn (Invoice $record): string => $record->displayNumber())
                    ->url(fn (Invoice $record): string => InvoiceResource::getUrl('view', ['record' => $record])),
                TextColumn::make('issue_date')->date('d M Y')->placeholder('—'),
                TextColumn::make('total')
                    ->state(fn (Invoice $record): string => $record->totalDisplay()),
                TextColumn::make('paid')
                    ->state(fn (Invoice $record): string => $record->paidDisplay()),
                TextColumn::make('outstanding')
                    ->state(fn (Invoice $record): string => $record->outstandingDisplay()),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn ($state, Invoice $record): string => $record->isOverdue()
                        ? 'Overdue'
                        : ($record->status instanceof InvoiceStatus ? $record->status->label() : '—'))
                    ->color(fn ($state, Invoice $record): string => $record->isOverdue()
                        ? 'danger'
                        : ($record->status instanceof InvoiceStatus ? $record->status->color() : 'gray')),
            ])
            ->headerActions([
                Action::make('createInvoice')
                    ->label('Create invoice')
                    ->icon(Heroicon::OutlinedPlus)
                    ->visible(function (): bool {
                        /** @var Booking $booking */
                        $booking = $this->getOwnerRecord();

                        return $booking->canCreateInvoice();
                    })
                    ->action(function (): void {
                        /** @var Booking $booking */
                        $booking = $this->getOwnerRecord();

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

                        redirect(InvoiceResource::getUrl('edit', ['record' => $invoice]));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Invoice $record): string => $record->isDraft()
                        ? InvoiceResource::getUrl('edit', ['record' => $record])
                        : InvoiceResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
