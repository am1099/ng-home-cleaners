<?php

namespace App\Filament\Resources\QuoteRequests\Schemas;

use App\Enums\ArrivalWindow;
use App\Enums\CleaningFrequency;
use App\Enums\PropertyType;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Filament\Support\MoneyInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Pipeline')
                ->columns(3)
                ->schema([
                    Select::make('status')
                        ->options(QuoteRequestStatus::options())
                        ->required()
                        ->native(false),
                    MoneyInput::make('final_quote_amount_pence', 'Final quoted amount')
                        ->required(false),
                    Textarea::make('internal_notes')
                        ->label('Internal notes')
                        ->rows(5)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function configureManualCreate(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            Section::make('Source & service')
                ->columns(3)
                ->schema([
                    Select::make('source')
                        ->options(QuoteRequestSource::manualOptions())
                        ->default(QuoteRequestSource::Phone->value)
                        ->required()
                        ->native(false),
                    Select::make('service_id')
                        ->label('Service')
                        ->relationship('service', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),
                    Select::make('status')
                        ->options(QuoteRequestStatus::options())
                        ->default(QuoteRequestStatus::New->value)
                        ->required()
                        ->native(false),
                    TextInput::make('guide_estimate_headline')
                        ->label('Guide estimate note')
                        ->placeholder('To be quoted')
                        ->maxLength(255)
                        ->columnSpan(2),
                    MoneyInput::make('final_quote_amount_pence', 'Final quoted amount')
                        ->required(false),
                ]),

            Section::make('Customer')
                ->columns(3)
                ->schema([
                    Select::make('customer_id')
                        ->label('Link existing customer (optional)')
                        ->relationship('customer', 'email')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->fullName().' · '.($record->email ?: $record->phone_display))
                        ->searchable(['first_name', 'last_name', 'email', 'phone_display', 'phone_normalized'])
                        ->preload()
                        ->helperText('Leave blank to match by email/phone or create a new customer. Conflicting phone+email pairs are not merged.')
                        ->columnSpanFull(),
                    TextInput::make('first_name')->required()->maxLength(100),
                    TextInput::make('last_name')->required()->maxLength(100),
                    TextInput::make('phone')->required()->tel()->maxLength(30),
                    TextInput::make('email')->email()->maxLength(255),
                    TextInput::make('postcode')->maxLength(10),
                    TextInput::make('city')->maxLength(100)->default('Nottingham'),
                    TextInput::make('address_line1')->label('Address line 1')->maxLength(255)->columnSpan(2),
                    TextInput::make('address_line2')->label('Address line 2')->maxLength(255),
                ]),

            Section::make('Visit & property')
                ->columns(3)
                ->schema([
                    DatePicker::make('preferred_date'),
                    Select::make('arrival_window')
                        ->options(collect(ArrivalWindow::cases())->mapWithKeys(
                            fn (ArrivalWindow $window) => [$window->value => $window->label()],
                        ))
                        ->native(false),
                    Select::make('frequency')
                        ->options(collect(CleaningFrequency::cases())->mapWithKeys(
                            fn (CleaningFrequency $frequency) => [$frequency->value => $frequency->label()],
                        ))
                        ->native(false),
                    Select::make('property_type')
                        ->options(collect(PropertyType::cases())->mapWithKeys(
                            fn (PropertyType $type) => [$type->value => $type->label()],
                        ))
                        ->native(false),
                    TextInput::make('bedrooms')->numeric()->minValue(0)->maxValue(5),
                    TextInput::make('floors')->numeric()->minValue(1)->maxValue(5),
                    TextInput::make('bathrooms')->numeric()->minValue(0)->maxValue(6),
                    TextInput::make('wcs')->label('WCs')->numeric()->minValue(0)->maxValue(4),
                    TextInput::make('kitchens')->numeric()->minValue(0)->maxValue(3),
                    TextInput::make('reception_rooms')->numeric()->minValue(0)->maxValue(6),
                ]),

            Section::make('Notes')
                ->schema([
                    Textarea::make('condition_notes')->rows(3),
                    Textarea::make('parking_notes')->rows(2),
                    Textarea::make('access_notes')->rows(2),
                    Textarea::make('internal_notes')->label('Internal notes')->rows(4),
                ]),
        ]);
    }
}
