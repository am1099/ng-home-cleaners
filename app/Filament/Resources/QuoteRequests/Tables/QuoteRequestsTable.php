<?php

namespace App\Filament\Resources\QuoteRequests\Tables;

use App\Enums\ArrivalWindow;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Filament\Support\StandardRecordActions;
use App\Pricing\Money;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuoteRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->label('Reference')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->copyable(),
                TextColumn::make('submitted_at')
                    ->label('Created')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->state(fn ($record) => $record->fullName())
                    ->description(fn ($record) => $record->email ?: $record->phone)
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function (Builder $inner) use ($search): void {
                            $inner->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereRaw("first_name || ' ' || last_name like ?", ["%{$search}%"])
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        });
                    }),
                TextColumn::make('source')
                    ->badge()
                    ->formatStateUsing(fn (QuoteRequestSource $state): string => $state->label())
                    ->color(fn (QuoteRequestSource $state): string => $state->color()),
                TextColumn::make('service.name')
                    ->label('Service')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('postcode')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('preferred_date')
                    ->label('Preferred date')
                    ->date('d M Y')
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('arrival_window')
                    ->label('Arrival')
                    ->formatStateUsing(fn (?string $state): string => ArrivalWindow::tryFrom((string) $state)?->label() ?? '—')
                    ->toggleable(),
                TextColumn::make('guide_estimate_headline')
                    ->label('Guide estimate')
                    ->wrap()
                    ->placeholder('—'),
                TextColumn::make('final_quote_amount_pence')
                    ->label('Final quote')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? Money::formatPence($state) : '—')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (QuoteRequestStatus $state): string => $state->label())
                    ->color(fn (QuoteRequestStatus $state): string => $state->color()),
            ])
            ->defaultSort('submitted_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options(QuoteRequestStatus::options()),
                SelectFilter::make('source')
                    ->options(QuoteRequestSource::options()),
                SelectFilter::make('service_id')
                    ->label('Service')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->preload(),
                Filter::make('preferred_date')
                    ->label('Preferred date')
                    ->form([
                        DatePicker::make('preferred_from')->label('From'),
                        DatePicker::make('preferred_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['preferred_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('preferred_date', '>=', $date))
                            ->when($data['preferred_until'] ?? null, fn (Builder $q, $date) => $q->whereDate('preferred_date', '<=', $date));
                    }),
                Filter::make('submitted_at')
                    ->label('Submitted date')
                    ->form([
                        DatePicker::make('submitted_from')->label('From'),
                        DatePicker::make('submitted_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['submitted_from'] ?? null, fn (Builder $q, $date) => $q->whereDate('submitted_at', '>=', $date))
                            ->when($data['submitted_until'] ?? null, fn (Builder $q, $date) => $q->whereDate('submitted_at', '<=', $date));
                    }),
            ])
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
