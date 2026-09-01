<?php

namespace App\Filament\Resources\LegalPages;

use App\Filament\Resources\LegalPages\Pages\CreateLegalPage;
use App\Filament\Resources\LegalPages\Pages\EditLegalPage;
use App\Filament\Resources\LegalPages\Pages\ListLegalPages;
use App\Filament\Resources\LegalPages\Pages\ViewLegalPage;
use App\Filament\Resources\LegalPages\Schemas\LegalPageForm;
use App\Filament\Resources\LegalPages\Tables\LegalPagesTable;
use App\Models\LegalPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LegalPageResource extends Resource
{
    protected static ?string $model = LegalPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|\UnitEnum|null $navigationGroup = 'Website';

    protected static ?int $navigationSort = 7;

    protected static ?string $navigationLabel = 'Legal pages';

    protected static ?string $modelLabel = 'legal page';

    protected static ?string $pluralModelLabel = 'legal pages';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return LegalPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LegalPagesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLegalPages::route('/'),
            'create' => CreateLegalPage::route('/create'),
            'view' => ViewLegalPage::route('/{record}'),
            'edit' => EditLegalPage::route('/{record}/edit'),
        ];
    }
}
