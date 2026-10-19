<?php

namespace App\Filament\Resources\BookingConfirmations;

use App\Filament\Resources\BookingConfirmations\Pages\EditBookingConfirmation;
use App\Filament\Resources\BookingConfirmations\Pages\ListBookingConfirmations;
use App\Filament\Resources\BookingConfirmations\Pages\ViewBookingConfirmation;
use App\Filament\Resources\BookingConfirmations\Schemas\BookingConfirmationForm;
use App\Filament\Resources\BookingConfirmations\Schemas\BookingConfirmationInfolist;
use App\Filament\Resources\BookingConfirmations\Tables\BookingConfirmationsTable;
use App\Models\BookingConfirmation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BookingConfirmationResource extends Resource
{
    protected static ?string $model = BookingConfirmation::class;

    protected static ?string $slug = 'booking-confirmations';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static string|\UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Booking confirmations';

    protected static ?string $modelLabel = 'booking confirmation';

    protected static ?string $recordTitleAttribute = 'full_name';

    public static function form(Schema $schema): Schema
    {
        return BookingConfirmationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BookingConfirmationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BookingConfirmationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBookingConfirmations::route('/'),
            'view' => ViewBookingConfirmation::route('/{record}'),
            'edit' => EditBookingConfirmation::route('/{record}/edit'),
        ];
    }
}
