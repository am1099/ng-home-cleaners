<?php

namespace App\Filament\Pages;

use App\Models\GoogleReportingConnection;
use App\Services\Analytics\BusinessPerformance;
use App\Services\Analytics\EnquiryAttribution;
use App\Services\Analytics\ReportingPeriod;
use App\Services\Analytics\ServicePerformance;
use App\Services\GoogleAdsReport;
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

    protected static ?string $title = 'Business performance';

    protected static ?string $slug = 'analytics';

    protected string $view = 'filament.pages.analytics';

    public const SECTIONS = [
        'enquiries',
        'advertising',
        'attribution',
        'services',
        'sources',
        'profile',
        'technical',
    ];

    #[Url]
    public int $days = 30;

    /** @var list<string> */
    public array $sectionOrder = [];

    public function mount(): void
    {
        $saved = auth()->user()?->analytics_layout;

        $this->sectionOrder = $this->normaliseOrder(is_array($saved) ? $saved : []);
    }

    public function range(int $days): void
    {
        $this->days = in_array($days, [7, 30, 90], true) ? $days : 30;
    }

    public function updatedSectionOrder(): void
    {
        $clean = $this->normaliseOrder($this->sectionOrder);

        auth()->user()?->update([
            'analytics_layout' => $clean,
        ]);

        if ($clean !== $this->sectionOrder) {
            $this->sectionOrder = $clean;
        }

        $this->skipRender();
    }

    /**
     * @param  list<mixed>  $order
     * @return list<string>
     */
    private function normaliseOrder(array $order): array
    {
        $clean = [];

        foreach ($order as $id) {
            if (is_string($id) && in_array($id, self::SECTIONS, true) && ! in_array($id, $clean, true)) {
                $clean[] = $id;
            }
        }

        foreach (self::SECTIONS as $id) {
            if (! in_array($id, $clean, true)) {
                $clean[] = $id;
            }
        }

        return $clean;
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
    public function dashboard(): array
    {
        $period = ReportingPeriod::make($this->days);
        $ads = app(GoogleAdsReport::class)->forPeriod($period);

        return [
            'period' => $period,
            'business' => app(BusinessPerformance::class)->summarise($period),
            'services' => app(ServicePerformance::class)->rows($period),
            'ads' => $ads,
            'attribution' => app(EnquiryAttribution::class)->summarise(
                $period,
                $ads['spend_pence'],
                (bool) $ads['spend_is_pounds'],
            ),
            'activity' => app(SiteAnalytics::class)->report($this->days),
        ];
    }
}
