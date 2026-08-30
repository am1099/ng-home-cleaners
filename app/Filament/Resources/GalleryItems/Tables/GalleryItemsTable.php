<?php

namespace App\Filament\Resources\GalleryItems\Tables;

use App\Filament\Support\StandardRecordActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GalleryItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->height(56)
                    ->width(72)
                    ->extraImgAttributes([
                        'class' => 'rounded-lg object-cover',
                    ]),
                TextColumn::make('alt_text')->limit(30)->searchable(),
                TextColumn::make('service.name')->toggleable(),
                ToggleColumn::make('is_published')->label('Published'),
            ])
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
