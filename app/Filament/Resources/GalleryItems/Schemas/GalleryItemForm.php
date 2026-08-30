<?php

namespace App\Filament\Resources\GalleryItems\Schemas;

use App\Filament\Support\SecureImageUpload;
use App\Filament\Support\ServiceAreaSelect;
use App\Models\GalleryItem;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GalleryItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Media')
                ->columns(2)
                ->schema([
                    SecureImageUpload::make('image_path', 'gallery', 1600)
                        ->label('Image')
                        ->required(),
                    TextInput::make('alt_text')
                        ->label('Alt text')
                        ->maxLength(255)
                        ->helperText('Short description for accessibility and SEO. Required before publishing if you leave this blank after a bulk upload.')
                        ->required(fn (?GalleryItem $record): bool => $record !== null),
                    Textarea::make('caption')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
            Section::make('Links')
                ->columns(3)
                ->schema([
                    Select::make('service_id')
                        ->relationship('service', 'name')
                        ->searchable()
                        ->preload(),
                    ServiceAreaSelect::make(),
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
