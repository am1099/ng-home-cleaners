<?php

namespace App\Filament\Resources\Testimonials\Tables;

use App\Filament\Support\ResponsiveRecordTable;
use App\Filament\Support\StandardRecordActions;
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

class TestimonialsTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: [
                TextColumn::make('sort_order')->label('#')->sortable(),
                TextColumn::make('customer_name')->searchable(),
                TextColumn::make('rating'),
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
                        TextColumn::make('rating')
                            ->description('Rating', position: 'above')
                            ->weight(FontWeight::SemiBold)
                            ->grow(false),
                        TextColumn::make('location')
                            ->description('Location', position: 'above')
                            ->placeholder('—'),
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
            ])
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
