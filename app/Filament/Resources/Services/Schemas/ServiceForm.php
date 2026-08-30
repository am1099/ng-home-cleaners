<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ServiceIcon;
use App\Filament\Resources\Services\RelationManagers\ExclusionsRelationManager;
use App\Filament\Resources\Services\RelationManagers\FaqsRelationManager;
use App\Filament\Resources\Services\RelationManagers\InclusionsRelationManager;
use App\Filament\Support\AutoSlug;
use App\Filament\Support\SecureImageUpload;
use App\Models\Service;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\View;
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
                                            ->label('Hero image')
                                            ->helperText('Optional full-width photo on the service page. Leave blank to use the card image.'),
                                        SecureImageUpload::make('card_image', 'services/cards', 1200)
                                            ->label('Card image')
                                            ->required()
                                            ->helperText('Required. This is the listing photo — it fills the top 40% of the service card.'),
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
                        Tab::make('Service details')
                            ->schema([
                                Section::make('Inclusions')
                                    ->description('Checklist items shown under “What is included” on this service’s public page.')
                                    ->collapsed()
                                    ->schema(self::relationDrawer(
                                        InclusionsRelationManager::class,
                                        'Save the service first, then add inclusions here.',
                                    )),
                                Section::make('Exclusions')
                                    ->description('Tasks outside the standard checklist. Shown under “What is not included”.')
                                    ->collapsed()
                                    ->schema(self::relationDrawer(
                                        ExclusionsRelationManager::class,
                                        'Save the service first, then add exclusions here.',
                                    )),
                                Section::make('FAQs')
                                    ->description('Questions shown on this service’s public page. Homepage FAQs are seeded separately.')
                                    ->collapsed()
                                    ->schema(self::relationDrawer(
                                        FaqsRelationManager::class,
                                        'Save the service first, then add FAQs here.',
                                    )),
                                Section::make('Optional add-ons')
                                    ->description('Attach existing extras from Pricing → Add-ons. This does not create a new product — tick those you want on the estimate form for this service.')
                                    ->collapsed()
                                    ->schema([
                                        CheckboxList::make('addons')
                                            ->relationship(titleAttribute: 'label')
                                            ->searchable()
                                            ->bulkToggleable()
                                            ->columns(2),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    /**
     * @return array<int, Text|View>
     */
    private static function relationDrawer(string $manager, string $createHint): array
    {
        return [
            Text::make($createHint)
                ->visible(fn (mixed $livewire): bool => ! self::hasPersistedRecord($livewire)),
            View::make('filament.services.embedded-relation')
                ->visible(fn (mixed $livewire): bool => self::hasPersistedRecord($livewire))
                ->viewData(function (mixed $livewire) use ($manager): array {
                    return [
                        'component' => $manager,
                        'ownerRecord' => $livewire->getRecord(),
                        'pageClass' => $livewire::class,
                    ];
                }),
        ];
    }

    private static function hasPersistedRecord(mixed $livewire): bool
    {
        if (! is_object($livewire) || ! method_exists($livewire, 'getRecord')) {
            return false;
        }

        $record = $livewire->getRecord();

        return $record instanceof Service && $record->exists;
    }
}
