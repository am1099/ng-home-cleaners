<?php

namespace App\Filament\Pages;

use App\Filament\Support\SecureImageUpload;
use App\Models\SiteSetting;
use App\Services\SiteSettingsService;
use Filament\Actions\Action;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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

class SiteSettings extends Page
{
    use CanUseDatabaseTransactions;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Site settings';

    protected static ?string $slug = 'site-settings';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public SiteSetting $record;

    public function mount(): void
    {
        $this->record = SiteSetting::instance();

        $data = $this->record->toArray();
        $data['opening_hours_summary'] = $this->record->opening_hours['summary'] ?? '';

        $this->form->fill($data);
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();

            if (isset($data['opening_hours_summary'])) {
                $data['opening_hours'] = ['summary' => $data['opening_hours_summary']];
                unset($data['opening_hours_summary']);
            }

            $this->record->update($data);

            app(SiteSettingsService::class)->forget();

            $this->commitDatabaseTransaction();
        } catch (Halt) {
            $this->rollBackDatabaseTransaction();

            return;
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Business')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('business_name')->required()->columnSpan(2),
                                        Textarea::make('business_address')->rows(2)->columnSpan(1),
                                        SecureImageUpload::make('logo_path', 'brand', 800)->label('Logo'),
                                        SecureImageUpload::make('favicon_path', 'brand', 256)->label('Favicon'),
                                        Textarea::make('service_area_summary')->rows(2)->columnSpan(1),
                                    ]),
                            ]),
                        Tab::make('Contact')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('phone')->required()->tel(),
                                        TextInput::make('email')->required()->email(),
                                        TextInput::make('whatsapp_number'),
                                        TextInput::make('whatsapp_url')->url()->columnSpan(2),
                                        TextInput::make('opening_hours_summary')
                                            ->label('Opening hours summary')
                                            ->helperText('Example: Every day, 8am–7pm')
                                            ->formatStateUsing(fn ($state, $record) => is_array($record?->opening_hours) ? ($record->opening_hours['summary'] ?? '') : '')
                                            ->dehydrated(false),
                                        TagsInput::make('lead_notification_emails')
                                            ->label('Lead notification emails')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Trust')
                            ->schema([
                                Section::make('Display toggles')
                                    ->columns(3)
                                    ->schema([
                                        Toggle::make('show_google_reviews')->label('Show Google reviews')->inline(false),
                                        Toggle::make('show_dbs_statement')->label('Show DBS statement')->inline(false),
                                        Toggle::make('show_insurance_statement')->label('Show insurance statement')->inline(false),
                                    ]),
                                Section::make('Copy')
                                    ->columns(2)
                                    ->schema([
                                        Textarea::make('dbs_statement')->rows(2),
                                        TextInput::make('insurance_amount'),
                                        Textarea::make('insurance_statement')->rows(2)->columnSpanFull(),
                                        Textarea::make('guarantee_statement')->rows(2)->columnSpanFull(),
                                    ]),
                            ]),
                        Tab::make('Homepage')
                            ->schema([
                                Section::make('Hero')
                                    ->description('Homepage hero title, supporting text, and the circular image on the right.')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('home_hero_title')
                                            ->label('Hero title')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),
                                        Textarea::make('home_hero_subtitle')
                                            ->label('Hero description')
                                            ->rows(3)
                                            ->required()
                                            ->columnSpanFull(),
                                        SecureImageUpload::make('home_hero_image', 'brand/hero', 1600)
                                            ->label('Hero image')
                                            ->helperText('Shown in the large circular frame on the right of the homepage hero. Use a bright, tidy interior photo.')
                                            ->columnSpanFull(),
                                    ]),
                                Section::make('Visibility')
                                    ->columns(2)
                                    ->schema([
                                        Toggle::make('show_recent_work')
                                            ->label('Show Recent Work section')
                                            ->helperText('Before-and-after cards on the homepage. Hidden automatically when there are no published items.')
                                            ->inline(false),
                                    ]),
                            ]),
                        Tab::make('SEO & social')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('default_seo_title')->columnSpan(2),
                                        SecureImageUpload::make('default_og_image', 'brand/og', 1200)
                                            ->label('Default social share image'),
                                        Textarea::make('default_seo_description')->rows(3)->columnSpanFull(),
                                        TextInput::make('google_business_url')->url(),
                                        TextInput::make('social_links.facebook')->label('Facebook URL')->url(),
                                        TextInput::make('social_links.instagram')->label('Instagram URL')->url(),
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
                            ->label('Save settings')
                            ->icon(\Filament\Support\Icons\Heroicon::OutlinedCheck)
                            ->submit('save')
                            ->keyBindings(['mod+s']),
                    ]),
                ]),
        ]);
    }

    public function getTitle(): string|Htmlable
    {
        return 'Site settings';
    }
}
