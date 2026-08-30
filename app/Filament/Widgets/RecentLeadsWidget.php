<?php

namespace App\Filament\Widgets;

use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Models\QuoteRequest;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentLeadsWidget extends TableWidget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected static ?string $heading = 'Recent leads';

    protected int|string|array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                QuoteRequest::query()
                    ->with(['service:id,name', 'customer:id,first_name,last_name'])
                    ->latest('submitted_at')
                    ->limit(8),
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('reference')
                    ->label('Ref')
                    ->url(fn (QuoteRequest $record): string => QuoteRequestResource::getUrl('view', ['record' => $record])),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->state(fn (QuoteRequest $record): string => $record->fullName()),
                TextColumn::make('service.name')
                    ->label('Service')
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (QuoteRequestStatus $state): string => $state->label())
                    ->color(fn (QuoteRequestStatus $state): string => $state->color()),
                TextColumn::make('submitted_at')
                    ->label('When')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (QuoteRequest $record): string => QuoteRequestResource::getUrl('view', ['record' => $record])),
            ])
            ->headerActions([])
            ->emptyStateHeading('No leads yet')
            ->emptyStateDescription('New quote requests will appear here.');
    }
}
