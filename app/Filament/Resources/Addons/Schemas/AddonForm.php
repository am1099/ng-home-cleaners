<?php

namespace App\Filament\Resources\Addons\Schemas;

use App\Enums\AddonPricingUnit;
use App\Filament\Support\AutoSlug;
use App\Filament\Support\MoneyInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AddonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
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
                        ->helperText('Auto-filled from the name.'),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->required(),
                    TextInput::make('label')
                        ->required()
                        ->helperText('Customer-facing label in the estimate form.')
                        ->columnSpan(2),
                    Select::make('pricing_unit')
                        ->options(AddonPricingUnit::class)
                        ->required(),
                    Textarea::make('description')
                        ->rows(2)
                        ->columnSpan(3),
                    Textarea::make('disclaimer')
                        ->rows(3)
                        ->helperText('Shown when this add-on is selected.')
                        ->columnSpan(3),
                ]),
            Section::make('Pricing')
                ->columns(2)
                ->schema([
                    MoneyInput::make('price_pence', 'Guide minimum price')
                        ->helperText('Lower bound used in estimates and shown to customers.'),
                    MoneyInput::make('price_max_pence', 'Guide maximum price')
                        ->helperText('Upper bound used in estimates. Must match the minimum for a fixed-price extra.'),
                ]),
            Section::make('Visibility')
                ->columns(2)
                ->schema([
                    Toggle::make('show_from_prefix')
                        ->label('Show “from” prefix')
                        ->inline(false),
                    Toggle::make('is_active')
                        ->label('Active')
                        ->default(true)
                        ->inline(false),
                ]),
        ]);
    }
}
