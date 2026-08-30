<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Services\DashboardMetrics;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CrmStatsOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected ?string $heading = 'At a glance';

    protected ?string $description = 'Live figures from leads, bookings, and received payments.';

    /**
     * @var int | array<string, ?int> | null
     */
    protected int|array|null $columns = [
        'default' => 2,
        'md' => 3,
        'xl' => 5,
    ];

    protected function getStats(): array
    {
        $metrics = app(DashboardMetrics::class);
        $data = $metrics->snapshot();

        $leadsChange = $metrics->countChangeDescription(
            $data['leads_this_month'],
            $data['leads_last_month'],
        );

        $completedChange = $metrics->countChangeDescription(
            $data['completed_jobs_this_month'],
            $data['completed_jobs_last_month'],
        );

        $revenueChange = $metrics->percentChangeDescription(
            $data['revenue_this_month_pence'],
            $data['revenue_last_month_pence'],
        );

        $conversionDescription = $data['conversion_rate'] === null
            ? 'No won/lost outcomes this month yet'
            : $data['won_this_month'].' won of '.$data['decided_this_month'].' decided'
                .($metrics->percentChangeDescription($data['conversion_rate'], $data['conversion_rate_last_month'])
                    ? ' · '.$metrics->percentChangeDescription($data['conversion_rate'], $data['conversion_rate_last_month'])
                    : '');

        $mostRequested = $data['most_requested_service'];

        return [
            Stat::make('New leads', (string) $data['new_leads'])
                ->description('Waiting for first contact')
                ->descriptionIcon(Heroicon::OutlinedInbox)
                ->color('info')
                ->url(QuoteRequestResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'new'],
                    ],
                ])),

            Stat::make('Leads this month', (string) $data['leads_this_month'])
                ->description($leadsChange)
                ->descriptionIcon($this->trendIcon($data['leads_this_month'], $data['leads_last_month']))
                ->color('primary')
                ->url(QuoteRequestResource::getUrl('index')),

            Stat::make('Awaiting response', (string) $data['awaiting_response'])
                ->description('Quote sent — waiting on customer')
                ->descriptionIcon(Heroicon::OutlinedClock)
                ->color('warning')
                ->url(QuoteRequestResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'quote_sent'],
                    ],
                ])),

            Stat::make('Upcoming bookings', (string) $data['upcoming_bookings'])
                ->description('Scheduled from today')
                ->descriptionIcon(Heroicon::OutlinedCalendarDays)
                ->color('info')
                ->url(BookingResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'scheduled'],
                    ],
                ])),

            Stat::make('Completed jobs', (string) $data['completed_jobs_this_month'])
                ->description($completedChange)
                ->descriptionIcon($this->trendIcon($data['completed_jobs_this_month'], $data['completed_jobs_last_month']))
                ->color('success')
                ->url(BookingResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'completed'],
                    ],
                ])),

            Stat::make('Revenue this month', $metrics->formatMoney($data['revenue_this_month_pence']))
                ->description($revenueChange ?? 'Received payments only')
                ->descriptionIcon($this->trendIcon($data['revenue_this_month_pence'], $data['revenue_last_month_pence']))
                ->color('success')
                ->url(PaymentResource::getUrl('index')),

            Stat::make('Revenue all time', $metrics->formatMoney($data['revenue_all_time_pence']))
                ->description('Net of refunds and adjustments')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color('success')
                ->url(PaymentResource::getUrl('index')),

            Stat::make('Outstanding balance', $metrics->formatMoney($data['outstanding_balance_pence']))
                ->description('Agreed minus paid on active bookings')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($data['outstanding_balance_pence'] > 0 ? 'warning' : 'success')
                ->url(BookingResource::getUrl('index')),

            Stat::make(
                'Lead conversion',
                $data['conversion_rate'] === null ? '—' : $data['conversion_rate'].'%',
            )
                ->description($conversionDescription)
                ->descriptionIcon(Heroicon::OutlinedArrowTrendingUp)
                ->color('primary')
                ->url(QuoteRequestResource::getUrl('index', [
                    'tableFilters' => [
                        'status' => ['value' => 'won'],
                    ],
                ])),

            Stat::make(
                'Most requested service',
                $mostRequested['name'] ?? '—',
            )
                ->description($mostRequested
                    ? $mostRequested['count'].' lead'.($mostRequested['count'] === 1 ? '' : 's').' all time'
                    : 'No leads yet')
                ->descriptionIcon(Heroicon::OutlinedSparkles)
                ->color('gray')
                ->url($mostRequested
                    ? QuoteRequestResource::getUrl('index', [
                        'tableFilters' => [
                            'service_id' => ['value' => (string) $mostRequested['id']],
                        ],
                    ])
                    : QuoteRequestResource::getUrl('index')),
        ];
    }

    private function trendIcon(int|float $current, int|float $previous): Heroicon
    {
        if ((float) $current > (float) $previous) {
            return Heroicon::OutlinedArrowTrendingUp;
        }

        if ((float) $current < (float) $previous) {
            return Heroicon::OutlinedArrowTrendingDown;
        }

        return Heroicon::OutlinedMinus;
    }
}
