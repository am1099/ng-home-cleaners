<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use App\Filament\Support\ResponsiveRecordTable;
use App\Filament\Support\StandardRecordActions;
use App\Support\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: [
                TextColumn::make('sort_order')->label('#')->sortable(),
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk(Media::diskName())
                    ->height(56)
                    ->width(72)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                TextColumn::make('alt_text')->limit(30)->searchable(),
                TextColumn::make('service.name')->toggleable(),
                ToggleColumn::make('is_published')->label('Published'),
            ],
            cardColumns: [
                ResponsiveRecordTable::stack([
                    ImageColumn::make('image_path')
                        ->disk(Media::diskName())
                        ->height(140)
                        ->extraImgAttributes(['class' => 'w-full rounded-xl object-cover']),
                    Split::make([
                        TextColumn::make('alt_text')
                            ->weight(FontWeight::SemiBold)
                            ->wrap()
                            ->searchable(),
                        ToggleColumn::make('is_published')->label('Published')->grow(false),
                    ]),
                    ResponsiveRecordTable::meta(
                        TextColumn::make('service.name')->placeholder('All services'),
                    ),
                ]),
            ],
            cardGrid: ['default' => 1, 'sm' => 2, 'lg' => 3],
        );

        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_published'),
            ])
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
