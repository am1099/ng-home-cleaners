<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Contact')
                ->columns(3)
                ->schema([
                    TextInput::make('first_name')->required()->maxLength(100),
                    TextInput::make('last_name')->required()->maxLength(100),
                    TextInput::make('phone_display')
                        ->label('Phone')
                        ->required()
                        ->tel()
                        ->maxLength(30),
                    TextInput::make('email')
                        ->email()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('postcode')->maxLength(10),
                    TextInput::make('city')->maxLength(100),
                    TextInput::make('address_line1')->label('Address line 1')->maxLength(255)->columnSpan(2),
                    TextInput::make('address_line2')->label('Address line 2')->maxLength(255),
                    Textarea::make('notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
