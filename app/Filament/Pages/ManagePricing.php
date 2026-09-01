<?php

namespace App\Filament\Pages;

use App\Enums\ConditionFlag;
use App\Enums\PropertyType;
use App\Enums\RoomModifierType;
use App\Filament\Support\MoneyInput;
use App\Models\PricingBedroomRule;
use App\Models\PricingCondition;
use App\Models\PricingExtraRoom;
use App\Models\PricingSetting;
use App\Models\PricingStartingPrice;
use App\Models\Service;
use App\Pricing\PricingConfiguration;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;
use Illuminate\Support\HtmlString;

class ManagePricing extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyPound;

    protected static string|\UnitEnum|null $navigationGroup = 'Pricing';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Pricing';

    protected static ?string $slug = 'pricing';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /** @var Collection<int, Service> */
    public Collection $residentialServices;

    public function mount(): void
    {
        $this->residentialServices = Service::query()
            ->whereIn('slug', ['regular-clean', 'deep-clean', 'end-of-tenancy'])
            ->orderBy('sort_order')
            ->get();

        $this->form->fill($this->loadFormState());
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();
            $this->persistFormState($data);
            PricingConfiguration::forget();

            $this->commitDatabaseTransaction();
        } catch (Halt) {
            $this->rollBackDatabaseTransaction();

            return;
        }

        Notification::make()
            ->title('Pricing saved')
            ->success()
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('addons_note')
                    ->label('')
                    ->content(new HtmlString(
                        'Optional extras (ovens, wardrobes, etc.) are managed under <strong>Website → Add-ons</strong>. Those prices feed the estimator directly.'
                    )),
                Tabs::make('Pricing')
                    ->tabs([
                        Tab::make('Starting prices')
                            ->schema([
                                Tabs::make('Starting price services')
                                    ->vertical()
                                    ->persistTabInQueryString('starting-service')
                                    ->tabs($this->startingPriceTabs()),
                            ]),
                        Tab::make('Bedrooms')
                            ->schema([
                                Tabs::make('Bedroom services')
                                    ->vertical()
                                    ->persistTabInQueryString('bedroom-service')
                                    ->tabs($this->bedroomTabs()),
                            ]),
                        Tab::make('Extra rooms')
                            ->schema([
                                Tabs::make('Extra room services')
                                    ->vertical()
                                    ->persistTabInQueryString('extra-service')
                                    ->tabs($this->extraRoomTabs()),
                            ]),
                        Tab::make('Conditions')
                            ->schema([
                                Tabs::make('Condition services')
                                    ->vertical()
                                    ->persistTabInQueryString('condition-service')
                                    ->tabs($this->conditionTabs()),
                            ]),
                        Tab::make('Frequency')
                            ->schema([
                                Section::make('Regular clean discounts')
                                    ->description('Applied only to Regular Cleaning. Shown as plain percentages.')
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('weekly_discount_percent')
                                            ->label('Weekly')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%'),
                                        TextInput::make('fortnightly_discount_percent')
                                            ->label('Fortnightly')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%'),
                                        TextInput::make('monthly_discount_percent')
                                            ->label('Monthly')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%'),
                                    ]),
                            ]),
                        Tab::make('Advanced')
                            ->schema([
                                Section::make('Advanced')
                                    ->description('Secondary settings. Most owners can leave these as they are.')
                                    ->collapsed()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('included_floors_baseline')
                                            ->label('Floors included in starting price')
                                            ->numeric()
                                            ->minValue(1)
                                            ->required(),
                                        MoneyInput::make('rounding_step_pence', 'Round guide estimates to'),
                                        MoneyInput::make('regular_min_pence', 'Regular clean minimum “from” price'),
                                        TextInput::make('empty_percent')
                                            ->label('Empty property adjustment')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%')
                                            ->helperText('Use a negative number for a discount, e.g. −8.'),
                                        TextInput::make('furnished_percent')
                                            ->label('Fully furnished adjustment')
                                            ->numeric()
                                            ->required()
                                            ->suffix('%')
                                            ->helperText('e.g. 5 means +5%. Part furnished stays at 0%.'),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ])
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')
                            ->label('Save pricing')
                            ->icon(Heroicon::OutlinedCheck)
                            ->submit('save'),
                    ]),
                ]),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Pricing';
    }

    /**
     * @return list<Tab>
     */
    private function startingPriceTabs(): array
    {
        $tabs = [];

        foreach ($this->residentialServices as $service) {
            $fields = [];
            foreach ([PropertyType::Flat, PropertyType::House, PropertyType::Bungalow] as $type) {
                $key = $service->id.'_'.$type->value;
                $fields[] = MoneyInput::make("starting.{$key}_min", $type->label().' — from');
                $fields[] = MoneyInput::make("starting.{$key}_max", $type->label().' — to');
            }

            $tabs[] = Tab::make($service->name)
                ->schema([
                    Section::make()
                        ->columns(2)
                        ->schema($fields),
                ]);
        }

        return $tabs;
    }

    /**
     * @return list<Tab>
     */
    private function bedroomTabs(): array
    {
        $tabs = [];

        foreach ($this->residentialServices as $service) {
            $tabs[] = Tab::make($service->name)
                ->schema([
                    Section::make()
                        ->columns(3)
                        ->schema([
                            TextInput::make("bedrooms.{$service->id}_included")
                                ->label('Bedrooms included in starting price')
                                ->numeric()
                                ->minValue(0)
                                ->maxValue(5)
                                ->required(),
                            MoneyInput::make("bedrooms.{$service->id}_extra_min", 'Each extra bedroom — from'),
                            MoneyInput::make("bedrooms.{$service->id}_extra_max", 'Each extra bedroom — to'),
                        ]),
                ]);
        }

        return $tabs;
    }

    /**
     * @return list<Tab>
     */
    private function extraRoomTabs(): array
    {
        $tabs = [];

        foreach ($this->residentialServices as $service) {
            $fields = [];
            foreach (RoomModifierType::cases() as $room) {
                $key = $service->id.'_'.$room->value;
                $fields[] = MoneyInput::make("rooms.{$key}_min", $room->label().' — from');
                $fields[] = MoneyInput::make("rooms.{$key}_max", $room->label().' — to');
            }

            $tabs[] = Tab::make($service->name)
                ->schema([
                    Section::make()
                        ->columns(2)
                        ->schema($fields),
                ]);
        }

        return $tabs;
    }

    /**
     * @return list<Tab>
     */
    private function conditionTabs(): array
    {
        $tabs = [];

        foreach ($this->residentialServices as $service) {
            $fields = [];
            foreach (ConditionFlag::cases() as $flag) {
                $key = $service->id.'_'.$flag->value;
                $fields[] = MoneyInput::make("conditions.{$key}_min", $flag->label().' — from');
                $fields[] = MoneyInput::make("conditions.{$key}_max", $flag->label().' — to');
            }

            $tabs[] = Tab::make($service->name)
                ->schema([
                    Section::make()
                        ->columns(2)
                        ->schema($fields),
                ]);
        }

        return $tabs;
    }

    /**
     * @return array<string, mixed>
     */
    private function loadFormState(): array
    {
        $settings = PricingSetting::instance();
        $state = [
            'weekly_discount_percent' => $settings->weekly_discount_percent,
            'fortnightly_discount_percent' => $settings->fortnightly_discount_percent,
            'monthly_discount_percent' => $settings->monthly_discount_percent,
            'included_floors_baseline' => $settings->included_floors_baseline,
            'rounding_step_pence' => $settings->rounding_step_pence,
            'regular_min_pence' => $settings->regular_min_pence,
            'empty_percent' => (float) bcmul(bcsub((string) $settings->empty_multiplier, '1', 4), '100', 2),
            'furnished_percent' => (float) bcmul(bcsub((string) $settings->furnished_multiplier, '1', 4), '100', 2),
            'starting' => [],
            'bedrooms' => [],
            'rooms' => [],
            'conditions' => [],
        ];

        foreach (PricingStartingPrice::query()->get() as $row) {
            $key = $row->service_id.'_'.$row->property_type->value;
            $state['starting'][$key.'_min'] = $row->min_pence;
            $state['starting'][$key.'_max'] = $row->max_pence;
        }

        foreach (PricingBedroomRule::query()->get() as $row) {
            $state['bedrooms'][$row->service_id.'_included'] = $row->bedrooms_included;
            $state['bedrooms'][$row->service_id.'_extra_min'] = $row->extra_min_pence;
            $state['bedrooms'][$row->service_id.'_extra_max'] = $row->extra_max_pence;
        }

        foreach (PricingExtraRoom::query()->get() as $row) {
            $key = $row->service_id.'_'.$row->room_type->value;
            $state['rooms'][$key.'_min'] = $row->min_pence;
            $state['rooms'][$key.'_max'] = $row->max_pence;
        }

        foreach (PricingCondition::query()->get() as $row) {
            $key = $row->service_id.'_'.$row->condition_flag->value;
            $state['conditions'][$key.'_min'] = $row->min_pence;
            $state['conditions'][$key.'_max'] = $row->max_pence;
        }

        return $state;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function persistFormState(array $data): void
    {
        $emptyPercent = (string) ($data['empty_percent'] ?? -8);
        $furnishedPercent = (string) ($data['furnished_percent'] ?? 5);

        PricingSetting::instance()->update([
            'weekly_discount_percent' => $data['weekly_discount_percent'],
            'fortnightly_discount_percent' => $data['fortnightly_discount_percent'],
            'monthly_discount_percent' => $data['monthly_discount_percent'],
            'included_floors_baseline' => $data['included_floors_baseline'],
            'rounding_step_pence' => $data['rounding_step_pence'],
            'regular_min_pence' => $data['regular_min_pence'],
            'empty_multiplier' => bcadd('1', bcdiv($emptyPercent, '100', 6), 3),
            'furnished_multiplier' => bcadd('1', bcdiv($furnishedPercent, '100', 6), 3),
        ]);

        foreach ($this->residentialServices as $service) {
            foreach ([PropertyType::Flat, PropertyType::House, PropertyType::Bungalow] as $type) {
                $key = $service->id.'_'.$type->value;
                PricingStartingPrice::query()->updateOrCreate(
                    ['service_id' => $service->id, 'property_type' => $type],
                    [
                        'min_pence' => $data['starting'][$key.'_min'],
                        'max_pence' => $data['starting'][$key.'_max'],
                    ],
                );
            }

            PricingBedroomRule::query()->updateOrCreate(
                ['service_id' => $service->id],
                [
                    'bedrooms_included' => $data['bedrooms'][$service->id.'_included'],
                    'extra_min_pence' => $data['bedrooms'][$service->id.'_extra_min'],
                    'extra_max_pence' => $data['bedrooms'][$service->id.'_extra_max'],
                ],
            );

            foreach (RoomModifierType::cases() as $room) {
                $key = $service->id.'_'.$room->value;
                PricingExtraRoom::query()->updateOrCreate(
                    ['service_id' => $service->id, 'room_type' => $room],
                    [
                        'label' => $room->label(),
                        'min_pence' => $data['rooms'][$key.'_min'],
                        'max_pence' => $data['rooms'][$key.'_max'],
                        'sort_order' => array_search($room, RoomModifierType::cases(), true) + 1,
                    ],
                );
            }

            foreach (ConditionFlag::cases() as $flag) {
                $key = $service->id.'_'.$flag->value;
                PricingCondition::query()->updateOrCreate(
                    ['service_id' => $service->id, 'condition_flag' => $flag],
                    [
                        'min_pence' => $data['conditions'][$key.'_min'],
                        'max_pence' => $data['conditions'][$key.'_max'],
                    ],
                );
            }
        }
    }
}
