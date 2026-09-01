<?php

namespace App\Filament\Resources\LegalPages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LegalPageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Page')
                ->columns(2)
                ->schema([
                    Select::make('slug')
                        ->options([
                            'privacy' => 'Privacy',
                            'terms' => 'Terms',
                            'cookies' => 'Cookies',
                        ])
                        ->required()
                        ->unique(ignoreRecord: true),
                    TextInput::make('title')
                        ->required()
                        ->maxLength(255),
                    Textarea::make('content')
                        ->required()
                        ->rows(16)
                        ->columnSpanFull(),
                    TextInput::make('seo_title')
                        ->maxLength(255),
                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(true)
                        ->inline(false),
                    Textarea::make('seo_description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
