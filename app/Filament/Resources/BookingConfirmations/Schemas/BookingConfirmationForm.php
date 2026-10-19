<?php

namespace App\Filament\Resources\BookingConfirmations\Schemas;

use App\Enums\BookingConfirmationStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingConfirmationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Review')
                ->schema([
                    Select::make('status')
                        ->options(collect(BookingConfirmationStatus::cases())->mapWithKeys(
                            fn (BookingConfirmationStatus $status): array => [$status->value => $status->label()],
                        )->all())
                        ->required(),
                    Textarea::make('notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
