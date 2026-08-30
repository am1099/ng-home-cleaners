<?php

namespace App\Providers;

use App\Models\GalleryItem;
use App\Models\PricingBedroomRule;
use App\Models\PricingCondition;
use App\Models\PricingExtraRoom;
use App\Models\PricingSetting;
use App\Models\PricingStartingPrice;
use App\Models\SiteSetting;
use App\Observers\PricingCacheObserver;
use App\Observers\SiteSettingObserver;
use App\Pricing\AddonPriceFormatter;
use App\Pricing\PricingEngine;
use App\Services\SiteSettingsService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SiteSettingsService::class);
    }

    public function boot(): void
    {
        $this->configureWritableTempDirectory();
        $this->configureAdminDateFields();
        $this->configureAdminSelectFields();
        $this->configureAdminActionIcons();

        SiteSetting::observe(SiteSettingObserver::class);

        foreach ([
            PricingSetting::class,
            PricingStartingPrice::class,
            PricingBedroomRule::class,
            PricingExtraRoom::class,
            PricingCondition::class,
        ] as $model) {
            $model::observe(PricingCacheObserver::class);
        }

        $this->app->singleton(PricingEngine::class);
        $this->app->singleton(AddonPriceFormatter::class);

        View::composer(['layouts.public', 'layouts.quote', 'components.layouts.quote', 'components.public.*', 'home', 'pages.*'], function ($view): void {
            if (! $view->offsetExists('settings')) {
                $view->with('settings', app(SiteSettingsService::class)->get());
            }

            if (! $view->offsetExists('showGalleryNav')) {
                $view->with(
                    'showGalleryNav',
                    GalleryItem::query()->published()->exists(),
                );
            }
        });
    }

    /**
     * Point PHP/Livewire temp files at a project-owned directory.
     * On some Windows setups tmpfile() fails against the system TEMP folder
     * (antivirus / permissions), which crashes TemporaryUploadedFile.
     */
    private function configureWritableTempDirectory(): void
    {
        $tmp = storage_path('app/tmp');

        if (! is_dir($tmp)) {
            @mkdir($tmp, 0755, true);
        }

        if (! is_dir($tmp) || ! is_writable($tmp)) {
            return;
        }

        putenv('TMP='.$tmp);
        putenv('TEMP='.$tmp);
        putenv('TMPDIR='.$tmp);
        $_ENV['TMP'] = $tmp;
        $_ENV['TEMP'] = $tmp;
        $_ENV['TMPDIR'] = $tmp;
        $_SERVER['TMP'] = $tmp;
        $_SERVER['TEMP'] = $tmp;
        $_SERVER['TMPDIR'] = $tmp;
        @ini_set('upload_tmp_dir', $tmp);
    }

    /**
     * CRM dates use Filament’s styled picker (not native browser controls).
     * Seconds are never shown — staff pick date and, where needed, hour:minute only.
     */
    private function configureAdminDateFields(): void
    {
        DatePicker::configureUsing(function (DatePicker $component): void {
            $component
                ->native(false)
                ->displayFormat('d/m/Y')
                ->closeOnDateSelection();
        });

        DateTimePicker::configureUsing(function (DateTimePicker $component): void {
            $component
                ->native(false)
                ->seconds(false)
                ->displayFormat('d/m/Y H:i')
                ->closeOnDateSelection();
        });

        TimePicker::configureUsing(function (TimePicker $component): void {
            $component
                ->native(false)
                ->seconds(false)
                ->displayFormat('H:i');
        });
    }

    /**
     * CRM selects use Filament’s dropdown UI — never native browser &lt;select&gt;.
     */
    private function configureAdminSelectFields(): void
    {
        Select::configureUsing(function (Select $component): void {
            $component->native(false);
        });
    }

    /**
     * Filament 5 header Create/Edit/View/Delete actions only set table/grouped icons by default.
     * Give every resource header button a visible icon.
     */
    private function configureAdminActionIcons(): void
    {
        CreateAction::configureUsing(function (CreateAction $action): void {
            $action->icon(Heroicon::OutlinedPlus);
        });

        EditAction::configureUsing(function (EditAction $action): void {
            $action->icon(Heroicon::OutlinedPencilSquare);
        });

        ViewAction::configureUsing(function (ViewAction $action): void {
            $action->icon(Heroicon::OutlinedEye);
        });

        DeleteAction::configureUsing(function (DeleteAction $action): void {
            $action->icon(Heroicon::OutlinedTrash);
        });
    }
}
