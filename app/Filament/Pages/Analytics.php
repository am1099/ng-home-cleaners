<?php

namespace App\Filament\Pages;

use App\Models\GoogleReportingConnection;
use App\Services\GoogleReportingClient;
use App\Services\SiteAnalytics;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Url;

class Analytics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'CRM';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Analytics';

    protected static ?string $title = 'Analytics';

    protected static ?string $slug = 'analytics';

    protected string $view = 'filament.pages.analytics';

    #[Url]
    public int $days = 30;

    public function range(int $days): void
    {
        $this->days = in_array($days, [7, 30, 90], true) ? $days : 30;
    }

    protected function getHeaderActions(): array
    {
        $google = app(GoogleReportingClient::class);

        if (! $google->isConfigured() || GoogleReportingConnection::query()->exists()) {
            return [];
        }

        return [
            Action::make('connectGoogleReporting')
                ->label('Connect Google Ads and Analytics')
                ->url(route('google-reporting.connect')),
        ];
    }

    public function googleAccount(): array
    {
        return [
            'ads_customer_id' => config('services.google.ads_customer_id'),
            'analytics_property_id' => config('services.google.analytics_property_id'),
            'connected' => GoogleReportingConnection::query()->exists(),
            'configured' => app(GoogleReportingClient::class)->isConfigured(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function report(): array
    {
        return app(SiteAnalytics::class)->report($this->days);
    }
}
