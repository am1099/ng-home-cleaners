<?php

namespace App\Filament\Resources\Faqs\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('FAQ')
                ->columns(2)
                ->schema([
                    TextInput::make('question')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    Textarea::make('answer')
                        ->required()
                        ->rows(5)
                        ->columnSpanFull(),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(true)
                        ->inline(false),
                ]),
        ]);
    }
}
