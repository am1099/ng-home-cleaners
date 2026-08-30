<?php

namespace App\Filament\Resources\RecentWorks\Schemas;

use App\Filament\Support\SecureImageUpload;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecentWorkForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Images')
                ->columns(2)
                ->schema([
                    SecureImageUpload::make('before_image_path', 'recent-work', 1600)
                        ->label('Before image')
                        ->required(),
                    SecureImageUpload::make('after_image_path', 'recent-work', 1600)
                        ->label('After image')
                        ->required(),
                    TextInput::make('alt_text_before')
                        ->label('Before image alt text')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('alt_text_after')
                        ->label('After image alt text')
                        ->required()
                        ->maxLength(255),
                ]),
            Section::make('Copy')
                ->columns(2)
                ->schema([
                    TextInput::make('title')
                        ->required()
                        ->maxLength(120)
                        ->columnSpanFull(),
                    Textarea::make('description')
                        ->label('Short description')
                        ->helperText('Shown under the title, often in short uppercase style on the site.')
                        ->rows(2)
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->required(),
                ]),
            Section::make('Publishing')
                ->columns(2)
                ->schema([
                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(true)
                        ->inline(false),
                    DateTimePicker::make('published_at')
                        ->label('Published at')
                        ->seconds(false)
                        ->default(now()),
                ]),
        ]);
    }
}
