<?php

namespace App\Filament\Resources\Payments\Tables;

use App\Enums\PaymentMethod;
use App\Enums\PaymentType;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Support\ResponsiveRecordTable;
use App\Filament\Support\StandardRecordActions;
use App\Models\Payment;
use App\Pricing\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: self::tableColumns(),
            cardColumns: self::cardColumns(),
        );

        return $table
            ->defaultSort('paid_date', 'desc')
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

    /**
     * @return array<int, TextColumn>
     */
    private static function tableColumns(): array
    {
        return [
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
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function cardColumns(): array
    {
        return [
            ResponsiveRecordTable::stack([
                Split::make([
                    TextColumn::make('amount_pence')
                        ->formatStateUsing(fn (int $state): string => Money::formatPence($state))
                        ->weight(FontWeight::Bold)
                        ->size(TextSize::Large)
                        ->grow(false),
                    TextColumn::make('type')
                        ->badge()
                        ->formatStateUsing(fn (PaymentType $state): string => $state->label())
                        ->grow(false),
                ]),
                TextColumn::make('booking.customer.first_name')
                    ->formatStateUsing(fn ($state, Payment $record): string => $record->booking?->customer?->fullName() ?? '—')
                    ->weight(FontWeight::SemiBold)
                    ->description(fn (Payment $record): string => collect([
                        $record->booking?->reference,
                        $record->method instanceof PaymentMethod ? $record->method->label() : null,
                    ])->filter()->implode(' · ')),
                ResponsiveRecordTable::meta(
                    TextColumn::make('paid_date')->date('d M Y'),
                ),
            ]),
        ];
    }
}
