<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Customer')
                ->columns(2)
                ->schema([
                    TextEntry::make('full_name')
                        ->label('Name')
                        ->state(fn ($record) => $record->fullName()),
                    TextEntry::make('phone_display')->label('Phone')->copyable(),
                    TextEntry::make('email')->copyable()->placeholder('—'),
                    TextEntry::make('postcode')->placeholder('—'),
                    TextEntry::make('full_address')
                        ->label('Address')
                        ->state(fn ($record) => $record->fullAddress() ?: '—')
                        ->columnSpanFull(),
                    TextEntry::make('notes')->placeholder('No notes.')->columnSpanFull(),
                    TextEntry::make('created_at')->dateTime('d M Y H:i'),
                    TextEntry::make('updated_at')->dateTime('d M Y H:i'),
                ]),
        ]);
    }
}
