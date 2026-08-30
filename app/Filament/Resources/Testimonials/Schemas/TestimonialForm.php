<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Review')
                ->columns(3)
                ->schema([
                    TextInput::make('customer_name')
                        ->required()
                        ->columnSpan(2),
                    TextInput::make('rating')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(5)
                        ->required(),
                    Textarea::make('review')
                        ->required()
                        ->rows(4)
                        ->columnSpanFull(),
                    TextInput::make('location'),
                    Select::make('service_id')
                        ->relationship('service', 'name')
                        ->searchable()
                        ->preload(),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    TextInput::make('source')
                        ->default('Google')
                        ->required(),
                    TextInput::make('source_url')
                        ->url()
                        ->columnSpan(2),
                ]),
            Section::make('Visibility')
                ->columns(2)
                ->schema([
                    Toggle::make('is_published')
                        ->label('Published')
                        ->inline(false),
                    Toggle::make('is_demo')
                        ->label('Demo data (local only)')
                        ->helperText('Demo reviews must not appear on production.')
                        ->inline(false),
                ]),
        ]);
    }
}
