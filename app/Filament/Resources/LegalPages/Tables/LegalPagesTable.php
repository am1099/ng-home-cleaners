<?php

namespace App\Filament\Resources\LegalPages\Tables;

use App\Filament\Support\StandardRecordActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LegalPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('slug'),
                ToggleColumn::make('is_published')->label('Published'),
                TextColumn::make('updated_at')->since()->label('Updated'),
            ])
            ->defaultSort('slug')
            ->recordActions(StandardRecordActions::make())
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
