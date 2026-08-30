<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Pricing\Money;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuoteRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'quoteRequests';

    protected static ?string $title = 'Leads';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reference')
                    ->url(fn ($record) => QuoteRequestResource::getUrl('view', ['record' => $record])),
                TextColumn::make('submitted_at')->label('Created')->dateTime('d M Y'),
                TextColumn::make('source')
                    ->badge()
                    ->formatStateUsing(fn (QuoteRequestSource $state): string => $state->label()),
                TextColumn::make('service.name')->label('Service'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (QuoteRequestStatus $state): string => $state->label())
                    ->color(fn (QuoteRequestStatus $state): string => $state->color()),
                TextColumn::make('guide_estimate_headline')->label('Guide')->placeholder('—'),
                TextColumn::make('final_quote_amount_pence')
                    ->label('Final quote')
                    ->formatStateUsing(fn (?int $state): string => $state !== null ? Money::formatPence($state) : '—'),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => QuoteRequestResource::getUrl('view', ['record' => $record])),
            ]);
    }
}
