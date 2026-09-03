<?php

namespace App\Filament\Support;

use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class ResponsiveRecordTable
{
    /**
     * @param  array<int, mixed>  $tableColumns
     * @param  array<int, mixed>  $cardColumns
     * @param  array<string, int>|null  $cardGrid
     */
    public static function configure(
        Table $table,
        array $tableColumns,
        array $cardColumns,
        ?array $cardGrid = null,
    ): Table {
        $livewire = $table->getLivewire();
        $useCards = method_exists($livewire, 'isGridLayout') && $livewire->isGridLayout();

        return $table
            ->columns($useCards ? $cardColumns : $tableColumns)
            ->contentGrid($useCards
                ? ($cardGrid ?? [
                    'default' => 1,
                    'md' => 2,
                    'xl' => 2,
                ])
                : null)
            ->stackedOnMobile(false);
    }

    /**
     * Compact meta line used under card titles.
     */
    public static function meta(TextColumn $column): TextColumn
    {
        return $column
            ->size(TextSize::Small)
            ->color('gray')
            ->weight(FontWeight::Medium);
    }

    /**
     * Wrap card body columns in a consistent vertical stack.
     *
     * @param  array<int, mixed>  $columns
     */
    public static function stack(array $columns, int $space = 3): Stack
    {
        return Stack::make($columns)
            ->space($space)
            ->extraAttributes([
                'class' => 'ng-record-card',
            ]);
    }
}
