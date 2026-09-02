<?php

namespace App\Filament\Resources\QuoteRequests\Schemas;

use App\Enums\ArrivalWindow;
use App\Enums\CleaningFrequency;
use App\Enums\ConditionFlag;
use App\Enums\ExtraRoomType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Filament\Support\MoneyInput;
use App\Filament\Support\SecureImageUpload;
use App\Models\Addon;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuoteRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            ...self::pipelineSection(),
            ...self::sourceAndServiceSection(includeAllSources: true),
            ...self::customerSection(),
            ...self::visitAndPropertySection(),
            ...self::conditionAndExtrasSection(),
            ...self::photosSection(),
            ...self::notesSection(includeInternalNotes: false),
        ]);
    }

    public static function configureManualCreate(Schema $schema): Schema
    {
        return $schema->columns(1)->components([
            ...self::sourceAndServiceSection(includeAllSources: false),
            ...self::customerSection(),
            ...self::visitAndPropertySection(),
            ...self::conditionAndExtrasSection(),
            ...self::notesSection(includeInternalNotes: true),
        ]);
    }

    /**
     * @return list<Component>
     */
    private static function pipelineSection(): array
    {
        return [
            Section::make('Pipeline')
                ->columns(3)
                ->schema([
                    Select::make('status')
                        ->options(QuoteRequestStatus::options())
                        ->required()
                        ->native(false),
                    MoneyInput::make('final_quote_amount_pence', 'Final quoted amount')
                        ->required(false),
                    TextInput::make('guide_estimate_headline')
                        ->label('Guide estimate note')
                        ->maxLength(255)
                        ->columnSpan(2),
                    Textarea::make('guide_estimate_detail')
                        ->label('Guide estimate detail')
                        ->rows(2)
                        ->columnSpanFull(),
                    Textarea::make('internal_notes')
                        ->label('Internal notes')
                        ->rows(4)
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function sourceAndServiceSection(bool $includeAllSources): array
    {
        return [
            Section::make('Source & service')
                ->columns(3)
                ->schema([
                    Select::make('source')
                        ->options($includeAllSources
                            ? QuoteRequestSource::options()
                            : QuoteRequestSource::manualOptions())
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
                        ->native(false)
                        ->visible(! $includeAllSources),
                    TextInput::make('guide_estimate_headline')
                        ->label('Guide estimate note')
                        ->placeholder('To be quoted')
                        ->maxLength(255)
                        ->columnSpan(2)
                        ->visible(! $includeAllSources),
                    MoneyInput::make('final_quote_amount_pence', 'Final quoted amount')
                        ->required(false)
                        ->visible(! $includeAllSources),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function customerSection(): array
    {
        return [
            Section::make('Customer')
                ->columns(3)
                ->schema([
                    Select::make('customer_id')
                        ->label('Link existing customer (optional)')
                        ->relationship('customer', 'email')
                        ->getOptionLabelFromRecordUsing(fn ($record) => $record->fullName().' · '.($record->email ?: $record->phone_display))
                        ->searchable(['first_name', 'last_name', 'email', 'phone_display', 'phone_normalized'])
                        ->preload()
                        ->helperText('Leave blank to keep the current customer link. Conflicting phone+email pairs are not merged.')
                        ->columnSpanFull(),
                    TextInput::make('first_name')->required()->maxLength(100),
                    TextInput::make('last_name')->required()->maxLength(100),
                    TextInput::make('phone')->required()->tel()->maxLength(30),
                    TextInput::make('email')->email()->maxLength(255),
                    TextInput::make('postcode')->maxLength(10),
                    TextInput::make('city')->maxLength(100),
                    TextInput::make('address_line1')->label('Address line 1')->maxLength(255)->columnSpan(2),
                    TextInput::make('address_line2')->label('Address line 2')->maxLength(255),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function visitAndPropertySection(): array
    {
        return [
            Section::make('Visit & property')
                ->columns(3)
                ->schema([
                    DatePicker::make('preferred_date'),
                    Select::make('arrival_window')
                        ->options(ArrivalWindow::options())
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
                    Toggle::make('split_level_flat')
                        ->label('Split-level flat')
                        ->inline(false),
                    TextInput::make('bathrooms')->numeric()->minValue(0)->maxValue(6),
                    TextInput::make('wcs')->label('WCs')->numeric()->minValue(0)->maxValue(4),
                    TextInput::make('kitchens')->numeric()->minValue(0)->maxValue(3),
                    TextInput::make('reception_rooms')->numeric()->minValue(0)->maxValue(6),
                    CheckboxList::make('extra_rooms')
                        ->label('Extra rooms')
                        ->options(collect(ExtraRoomType::cases())->mapWithKeys(
                            fn (ExtraRoomType $room) => [$room->value => $room->label()],
                        ))
                        ->columns(2)
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function conditionAndExtrasSection(): array
    {
        return [
            Section::make('Condition & extras')
                ->columns(2)
                ->schema([
                    Select::make('property_status')
                        ->options(collect(PropertyStatus::cases())->mapWithKeys(
                            fn (PropertyStatus $status) => [$status->value => $status->label()],
                        ))
                        ->native(false),
                    CheckboxList::make('condition_flags')
                        ->label('Condition flags')
                        ->options(collect(ConditionFlag::cases())->mapWithKeys(
                            fn (ConditionFlag $flag) => [$flag->value => $flag->label()],
                        ))
                        ->columns(2)
                        ->columnSpanFull(),
                    CheckboxList::make('addon_ids')
                        ->label('Selected add-ons')
                        ->options(fn (): array => Addon::query()
                            ->orderBy('sort_order')
                            ->pluck('label', 'id')
                            ->all())
                        ->columns(2)
                        ->columnSpanFull()
                        ->dehydrateStateUsing(fn (?array $state): array => array_values(array_map(
                            'intval',
                            $state ?? [],
                        ))),
                    Textarea::make('condition_notes')->rows(3)->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function photosSection(): array
    {
        return [
            Section::make('Property photos')
                ->schema([
                    SecureImageUpload::make('property_photo_paths', 'quote-photos', 1600)
                        ->label('Photos')
                        ->multiple()
                        ->reorderable()
                        ->maxFiles(8)
                        ->helperText('Replace or add photos the customer sent over WhatsApp.')
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function notesSection(bool $includeInternalNotes): array
    {
        return [
            Section::make('Access notes')
                ->schema([
                    Textarea::make('parking_notes')->rows(2),
                    Textarea::make('access_notes')->rows(2),
                    ...($includeInternalNotes ? [
                        Textarea::make('internal_notes')
                            ->label('Internal notes')
                            ->rows(4),
                    ] : []),
                ]),
        ];
    }
}
