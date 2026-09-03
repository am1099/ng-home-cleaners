<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Support\ResponsiveRecordTable;
use App\Filament\Support\StandardRecordActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: self::tableColumns(),
            cardColumns: self::cardColumns(),
        );

        return $table
            ->defaultSort('updated_at', 'desc')
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
            TextColumn::make('name')
                ->label('Name')
                ->state(fn ($record) => $record->fullName())
                ->searchable(query: function (Builder $query, string $search): Builder {
                    return $query->where(function (Builder $inner) use ($search): void {
                        $inner->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhereRaw("(first_name || ' ' || last_name) like ?", ["%{$search}%"]);
                    });
                })
                ->sortable(['first_name', 'last_name']),
            TextColumn::make('phone_display')
                ->label('Phone')
                ->searchable(['phone_display', 'phone_normalized']),
            TextColumn::make('email')
                ->searchable()
                ->placeholder('—'),
            TextColumn::make('postcode')
                ->searchable()
                ->placeholder('—'),
            TextColumn::make('quote_requests_count')
                ->counts('quoteRequests')
                ->label('Leads'),
            TextColumn::make('updated_at')
                ->dateTime('d M Y')
                ->sortable(),
        ];
    }

    /**
     * @return array<int, mixed>
     */
    private static function cardColumns(): array
    {
        return [
            ResponsiveRecordTable::stack([
                TextColumn::make('name')
                    ->state(fn ($record) => $record->fullName())
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Large)
                    ->description(fn ($record): string => collect([
                        $record->postcode,
                        $record->city,
                    ])->filter()->implode(' · ')),
                TextColumn::make('phone_display')
                    ->label('Phone')
                    ->weight(FontWeight::Medium)
                    ->description(fn ($record): ?string => $record->email),
                Split::make([
                    TextColumn::make('quote_requests_count')
                        ->counts('quoteRequests')
                        ->description('Leads', position: 'above')
                        ->weight(FontWeight::SemiBold)
                        ->grow(false),
                    ResponsiveRecordTable::meta(
                        TextColumn::make('updated_at')
                            ->dateTime('d M Y')
                            ->alignEnd(),
                    ),
                ]),
            ]),
        ];
    }
}
