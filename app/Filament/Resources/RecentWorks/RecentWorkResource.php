<?php

namespace App\Filament\Resources\RecentWorks;

use App\Filament\Resources\RecentWorks\Pages\CreateRecentWork;
use App\Filament\Resources\RecentWorks\Pages\EditRecentWork;
use App\Filament\Resources\RecentWorks\Pages\ListRecentWorks;
use App\Filament\Resources\RecentWorks\Pages\ViewRecentWork;
use App\Filament\Resources\RecentWorks\Schemas\RecentWorkForm;
use App\Filament\Resources\RecentWorks\Tables\RecentWorksTable;
use App\Models\RecentWork;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RecentWorkResource extends Resource
{
    protected static ?string $model = RecentWork::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Recent work';

    protected static ?string $modelLabel = 'recent work';

    protected static ?string $pluralModelLabel = 'recent work';

    public static function form(Schema $schema): Schema
    {
        return RecentWorkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecentWorksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecentWorks::route('/'),
            'create' => CreateRecentWork::route('/create'),
            'view' => ViewRecentWork::route('/{record}'),
            'edit' => EditRecentWork::route('/{record}/edit'),
        ];
    }
}
