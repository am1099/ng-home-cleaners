<?php

namespace App\Filament\Resources\RecentWorks\Tables;

use App\Filament\Support\StandardRecordActions;
use App\Support\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RecentWorksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('#')->sortable(),
                ImageColumn::make('before_image_path')
                    ->label('Before')
                    ->disk(Media::diskName())
                    ->height(56)
                    ->width(72)
                    ->extraImgAttributes([
                        'class' => 'rounded-lg object-cover',
                    ]),
                ImageColumn::make('after_image_path')
                    ->label('After')
                    ->disk(Media::diskName())
                    ->height(56)
                    ->width(72)
                    ->extraImgAttributes([
                        'class' => 'rounded-lg object-cover',
                    ]),
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('description')->limit(30)->toggleable(),
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
