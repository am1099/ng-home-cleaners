<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\QuoteRequest;
use App\Models\SiteEvent;
use App\Models\Testimonial;
use App\Services\Analytics\ReportingPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SiteAnalytics
{
    public function __construct(private readonly GoogleReviewClient $google) {}

    /**
     * @return array<string, mixed>
     */
    public function report(int $days): array
    {
        $period = ReportingPeriod::make($days);
        $days = $period->days;
        $since = $period->start;
        $until = $period->end;
        $events = SiteEvent::query()->whereBetween('occurred_at', [$since, $until]);

        $visitors = SiteEvent::query()
            ->where('occurred_at', '>=', $since)
            ->whereNotNull('visitor_key')
            ->count(DB::raw('distinct visitor_key'));
        $views = (clone $events)->where('event', SiteEventRecorder::PAGE_VIEW)->count();
        $whatsapp = (clone $events)->where('event', SiteEventRecorder::WHATSAPP_OPENED)->count();
        $phone = (clone $events)->where('event', SiteEventRecorder::PHONE_CLICKED)->count();
        $formStarts = (clone $events)->where('event', SiteEventRecorder::FORM_STARTED)->count();

        $quotes = QuoteRequest::query()->whereBetween('submitted_at', [$since, $until])->get();

        return [
            'days' => $days,
            'since' => $since,
            'visitors' => $visitors,
            'views' => $views,
            'whatsapp' => $whatsapp,
            'phone' => $phone,
            'form_starts' => $formStarts,
            'quotes' => $quotes->count(),
            'top_pages' => $this->grouped($since, SiteEventRecorder::PAGE_VIEW),
            'whatsapp_pages' => $this->grouped($since, SiteEventRecorder::WHATSAPP_OPENED),
            'phone_pages' => $this->grouped($since, SiteEventRecorder::PHONE_CLICKED),
            'traffic' => $this->sources($since),
            'devices' => $this->devices($since),
            'quote_sources' => $this->quoteSources($quotes),
            'ads' => $this->ads($since, $quotes),
            'profile' => $this->profile($since),
        ];
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function grouped(Carbon $since, string $event): array
    {
        return SiteEvent::query()
            ->selectRaw('path, count(*) as total')
            ->where('occurred_at', '>=', $since)
            ->where('event', $event)
            ->groupBy('path')
            ->orderByDesc('total')
            ->limit(8)
            ->get()
            ->map(fn ($row): array => [
                'label' => (string) $row->path,
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function sources(Carbon $since): array
    {
        return SiteEvent::query()
            ->selectRaw('source, count(*) as total')
            ->where('occurred_at', '>=', $since)
            ->where('event', SiteEventRecorder::PAGE_VIEW)
            ->groupBy('source')
            ->orderByDesc('total')
            ->limit(6)
            ->get()
            ->map(fn ($row): array => [
                'label' => (string) ($row->source ?: 'Direct'),
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @return list<array{label: string, count: int}>
     */
    private function devices(Carbon $since): array
    {
        return SiteEvent::query()
            ->selectRaw('device, count(*) as total')
            ->where('occurred_at', '>=', $since)
            ->where('event', SiteEventRecorder::PAGE_VIEW)
            ->groupBy('device')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row): array => [
                'label' => ucfirst((string) ($row->device ?: 'desktop')),
                'count' => (int) $row->total,
            ])
            ->all();
    }

    /**
     * @param  Collection<int, QuoteRequest>  $quotes
     * @return list<array{label: string, count: int}>
     */
    private function quoteSources($quotes): array
    {
        return $quotes
            ->groupBy(fn (QuoteRequest $quote): string => $this->quoteLabel($quote))
            ->map(fn ($group, $label): array => ['label' => (string) $label, 'count' => $group->count()])
            ->sortByDesc('count')
            ->values()
            ->take(6)
            ->all();
    }

    private function quoteLabel(QuoteRequest $quote): string
    {
        $latest = is_array($quote->ad_attribution) ? ($quote->ad_attribution['latest'] ?? null) : null;

        if (! is_array($latest)) {
            return 'Not recorded';
        }

        if (filled($latest['gclid'] ?? null) || filled($latest['gbraid'] ?? null) || filled($latest['wbraid'] ?? null)) {
            return 'Google Ads';
        }

        if (filled($latest['utm_source'] ?? null)) {
            return (string) $latest['utm_source'];
        }

        return 'Direct';
    }

    /**
     * @param  Collection<int, QuoteRequest>  $quotes
     * @return array<string, int>
     */
    private function ads(Carbon $since, $quotes): array
    {
        $booked = Booking::query()
            ->where('created_at', '>=', $since)
            ->whereHas('quoteRequest', function ($query): void {
                $query->whereNotNull('ad_attribution');
            })
            ->count();

        return [
            'visits' => SiteEvent::query()
                ->where('occurred_at', '>=', $since)
                ->where('event', SiteEventRecorder::PAGE_VIEW)
                ->where('from_ads', true)
                ->count(),
            'quotes' => $quotes->filter(fn (QuoteRequest $quote): bool => $this->quoteLabel($quote) === 'Google Ads')->count(),
            'booked' => $booked,
            'whatsapp' => SiteEvent::query()
                ->where('occurred_at', '>=', $since)
                ->where('event', SiteEventRecorder::WHATSAPP_OPENED)
                ->where('from_ads', true)
                ->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(Carbon $since): array
    {
        $reviews = Testimonial::query()
            ->where('source', 'Google')
            ->where('is_demo', false)
            ->where(function ($query) use ($since): void {
                $query->where('reviewed_at', '>=', $since)->orWhere('created_at', '>=', $since);
            });

        $all = Testimonial::query()->where('source', 'Google')->where('is_demo', false);

        return [
            'connected' => $this->google->isConnected(),
            'new_reviews' => (clone $reviews)->count(),
            'average' => round((float) $all->avg('rating'), 1),
            'total_reviews' => $all->count(),
            'performance' => $this->googlePerformance($since),
        ];
    }

    /**
     * @return array{available: bool, message: string, metrics: array<string, int>}
     */
    private function googlePerformance(Carbon $since): array
    {
        if (! $this->google->isConnected()) {
            return [
                'available' => false,
                'message' => 'Connect Google Business Profile from Reviews to pull profile views and calls.',
                'metrics' => [],
            ];
        }

        return Cache::remember('gbp-performance-'.$since->toDateString(), now()->addHour(), function () use ($since): array {
            try {
                return $this->google->performanceSince($since);
            } catch (\Throwable $exception) {
                return [
                    'available' => false,
                    'message' => 'Google Business Profile is connected, but profile views could not be loaded. Reviews below are from the reviews already saved here.',
                    'metrics' => [],
                ];
            }
        });
    }
}
