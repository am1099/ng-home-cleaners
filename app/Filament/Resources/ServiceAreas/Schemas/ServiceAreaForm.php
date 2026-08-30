<?php

namespace App\Filament\Resources\ServiceAreas\Schemas;

use App\Filament\Support\AutoSlug;
use App\Filament\Support\SecureImageUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ServiceAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Tabs::make('Service area')
                ->tabs([
                    Tab::make('Overview')
                        ->schema([
                            Section::make('Basics')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('name')
                                        ->required()
                                        ->live(onBlur: true)
                                        ->afterStateUpdated(AutoSlug::fromName()),
                                    TextInput::make('slug')
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->alphaDash()
                                        ->helperText('Auto-filled from the name; edit only if you need a custom URL.'),
                                    TextInput::make('postcode_label')
                                        ->label('Postcode label')
                                        ->required()
                                        ->placeholder('NG1')
                                        ->helperText('Short district code shown in CRM lists and the calendar (e.g. NG1).'),
                                    Textarea::make('short_intro')
                                        ->label('Short intro')
                                        ->required()
                                        ->rows(3)
                                        ->helperText('Brief summary used on cards and at the top of the area page.')
                                        ->columnSpan(3),
                                    Textarea::make('content')
                                        ->label('Area page body')
                                        ->rows(6)
                                        ->helperText('Optional longer copy on this area’s public page, under the short intro. Use it for local landmarks, neighbourhood notes, or what customers in this district can expect. Leave blank if the short intro is enough.')
                                        ->columnSpan(3),
                                    Textarea::make('coverage_notes')
                                        ->label('Coverage notes')
                                        ->rows(3)
                                        ->helperText('Internal or on-page notes about streets, estates, or exceptions within this district.')
                                        ->columnSpan(3),
                                    Select::make('services')
                                        ->relationship('services', 'name')
                                        ->multiple()
                                        ->preload()
                                        ->columnSpan(3),
                                ]),
                            Section::make('Visibility')
                                ->columns(2)
                                ->schema([
                                    Toggle::make('is_active')
                                        ->label('Active on website')
                                        ->default(true)
                                        ->inline(false),
                                    TextInput::make('sort_order')
                                        ->numeric()
                                        ->default(0)
                                        ->required(),
                                ]),
                        ]),
                    Tab::make('SEO')
                        ->schema([
                            Section::make()
                                ->columns(2)
                                ->schema([
                                    TextInput::make('seo_title'),
                                    Textarea::make('seo_description')->rows(3)->columnSpan(2),
                                    SecureImageUpload::make('hero_image', 'areas/hero', 2000)
                                        ->label('Hero image')
                                        ->columnSpan(2),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }
}
