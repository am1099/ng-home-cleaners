<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ServiceIcon;
use App\Filament\Support\AutoSlug;
use App\Filament\Support\SecureImageUpload;
use App\Models\Service;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Tabs::make('Service')
                    ->tabs([
                        Tab::make('Overview')
                            ->schema([
                                Section::make('Basics')
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('name')
                                            ->required()
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(AutoSlug::fromName())
                                            ->columnSpan(1),
                                        TextInput::make('slug')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->alphaDash()
                                            ->helperText('Auto-filled from the name; edit only if you need a custom URL.')
                                            ->columnSpan(1),
                                        TextInput::make('sort_order')
                                            ->numeric()
                                            ->default(0)
                                            ->required()
                                            ->columnSpan(1),
                                        TextInput::make('card_title')
                                            ->required()
                                            ->columnSpan(2),
                                        TextInput::make('cta_label')
                                            ->label('CTA copy')
                                            ->placeholder('Book my first clean')
                                            ->columnSpan(1),
                                        Textarea::make('short_description')
                                            ->required()
                                            ->rows(3)
                                            ->columnSpan(3),
                                        Textarea::make('estimate_description')
                                            ->label('Estimate form description')
                                            ->required()
                                            ->rows(3)
                                            ->columnSpan(3),
                                        Textarea::make('full_description')
                                            ->rows(5)
                                            ->columnSpan(3),
                                    ]),
                                Section::make('Icon')
                                    ->description('Each service must use a unique icon. Icons already used by other services are hidden.')
                                    ->schema([
                                        ToggleButtons::make('icon')
                                            ->label('Service icon')
                                            ->options(function ($livewire): array {
                                                $record = method_exists($livewire, 'getRecord') ? $livewire->getRecord() : null;
                                                $keep = $record instanceof Service && $record->icon instanceof ServiceIcon
                                                    ? $record->icon->value
                                                    : null;

                                                return ServiceIcon::filamentLabels($keep);
                                            })
                                            ->icons(function ($livewire): array {
                                                $record = method_exists($livewire, 'getRecord') ? $livewire->getRecord() : null;
                                                $keep = $record instanceof Service && $record->icon instanceof ServiceIcon
                                                    ? $record->icon->value
                                                    : null;

                                                return ServiceIcon::filamentIcons($keep);
                                            })
                                            ->columns(5)
                                            ->gridDirection('row')
                                            ->required()
                                            ->helperText('Pick from the grid — shown as icons customers see on cards and the estimate form.'),
                                    ]),
                                Section::make('Visibility')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('is_active')
                                            ->label('Active on website')
                                            ->helperText('Inactive services are hidden from public pages and the estimator.')
                                            ->default(true)
                                            ->inline(false),
                                    ]),
                            ]),
                        Tab::make('Media')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        SecureImageUpload::make('hero_image', 'services/hero', 2000)
                                            ->label('Hero image'),
                                        SecureImageUpload::make('card_image', 'services/cards', 1200)
                                            ->label('Card image'),
                                        SecureImageUpload::make('og_image', 'services/og', 1200)
                                            ->label('Social share image'),
                                    ]),
                            ]),
                        Tab::make('SEO')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('seo_title')
                                            ->maxLength(255),
                                        Textarea::make('seo_description')
                                            ->rows(3)
                                            ->maxLength(320)
                                            ->columnSpan(2),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
