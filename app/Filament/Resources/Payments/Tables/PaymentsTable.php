<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Support\StandardRecordActions;
use App\Models\Payment;
use App\Pricing\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('paid_date', 'desc')
            ->columns([
                TextColumn::make('paid_date')->date('d M Y')->sortable(),
                TextColumn::make('booking.reference')
                    ->label('Booking')
                    ->searchable()
                    ->url(fn (Payment $record): string => BookingResource::getUrl('view', ['record' => $record->booking_id])),
                TextColumn::make('booking.customer.first_name')
                    ->label('Customer')
                    ->formatStateUsing(fn ($state, Payment $record): string => $record->booking?->customer?->fullName() ?? '—'),
                TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn (PaymentType $state): string => $state->label()),
                TextColumn::make('method')
                    ->formatStateUsing(fn (PaymentMethod $state): string => $state->label()),
                TextColumn::make('amount_pence')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state): string => Money::formatPence($state))
                    ->alignEnd()
                    ->sortable(),
                TextColumn::make('reference')->placeholder('—')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(PaymentType::options()),
                SelectFilter::make('method')->options(PaymentMethod::options()),
            ])
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
