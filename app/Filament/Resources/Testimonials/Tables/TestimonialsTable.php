<?php

namespace App\Filament\Resources\Testimonials\Tables;

use App\Filament\Support\ResponsiveRecordTable;
use App\Filament\Support\StandardRecordActions;
use App\Models\Testimonial;
use Asignua\FilamentRating\Columns\RatingColumn;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: [
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('customer_name')->searchable(),
                self::addedViaColumn(),
                RatingColumn::make('rating')->stars(5)->color('warning')->showValue(),
                TextColumn::make('location')->toggleable(),
                ToggleColumn::make('is_published')->label('Published'),
                IconColumn::make('is_demo')->label('Demo')->boolean(),
            ],
            cardColumns: [
                ResponsiveRecordTable::stack([
                    Split::make([
                        TextColumn::make('customer_name')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->searchable(),
                        ToggleColumn::make('is_published')->label('Published')->grow(false),
                    ]),
                    Split::make([
                        RatingColumn::make('rating')->stars(5)->color('warning')->showValue()->grow(false),
                        TextColumn::make('location')
                            ->description('Location', position: 'above'),
                        self::addedViaColumn()->grow(false),
                        IconColumn::make('is_demo')->label('Demo')->boolean()->grow(false),
                    ]),
                ]),
            ],
        );

        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_published'),
                TernaryFilter::make('is_demo'),
                TernaryFilter::make('google_review_id')
                    ->label('Added from Google')
                    ->placeholder('All reviews')
                    ->trueLabel('Google Business Profile')
                    ->falseLabel('Added in CRM')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('google_review_id'),
                        false: fn (Builder $query) => $query->whereNull('google_review_id'),
                    ),
            ])
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    private static function addedViaColumn(): TextColumn
    {
        return TextColumn::make('google_review_id')
            ->label('Added')
            ->badge()
            ->getStateUsing(fn (Testimonial $record): string => filled($record->google_review_id)
                ? 'Google Business Profile'
                : 'Added in CRM')
            ->color(fn (string $state): string => $state === 'Google Business Profile' ? 'success' : 'gray')
            ->tooltip(fn (string $state): ?string => $state === 'Google Business Profile'
                ? 'Imported automatically from the connected Google Business Profile.'
                : 'Typed in here, not imported from Google.');
    }
}
