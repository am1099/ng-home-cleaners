<?php

namespace App\Filament\Resources\RecentWorks\Tables;

use App\Filament\Support\ResponsiveRecordTable;
use App\Filament\Support\StandardRecordActions;
use App\Support\Media;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RecentWorksTable
{
    public static function configure(Table $table): Table
    {
        $table = ResponsiveRecordTable::configure(
            $table,
            tableColumns: [
                TextColumn::make('sort_order')->label('#')->sortable(),
                ImageColumn::make('before_image_path')
                    ->label('Before')
                    ->disk(Media::diskName())
                    ->height(56)
                    ->width(72)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                ImageColumn::make('after_image_path')
                    ->label('After')
                    ->disk(Media::diskName())
                    ->height(56)
                    ->width(72)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover']),
                TextColumn::make('title')->searchable()->limit(40),
                TextColumn::make('description')->limit(30)->toggleable(),
                ToggleColumn::make('is_published')->label('Published'),
            ],
            cardColumns: [
                ResponsiveRecordTable::stack([
                    Split::make([
                        ImageColumn::make('before_image_path')
                            ->disk(Media::diskName())
                            ->height(96)
                            ->extraImgAttributes(['class' => 'w-full rounded-lg object-cover']),
                        ImageColumn::make('after_image_path')
                            ->disk(Media::diskName())
                            ->height(96)
                            ->extraImgAttributes(['class' => 'w-full rounded-lg object-cover']),
                    ]),
                    Split::make([
                        TextColumn::make('title')
                            ->weight(FontWeight::Bold)
                            ->size(TextSize::Large)
                            ->wrap()
                            ->searchable(),
                        ToggleColumn::make('is_published')->label('Published')->grow(false),
                    ]),
                    ResponsiveRecordTable::meta(
                        TextColumn::make('description')->limit(80)->placeholder('—')->wrap(),
                    ),
                ]),
            ],
            cardGrid: ['default' => 1, 'md' => 2],
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
