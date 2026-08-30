<?php

namespace App\Filament\Resources\Customers\Tables;

use App\Filament\Support\StandardRecordActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
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
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
