<?php

namespace App\Services;

use App\Models\GoogleReportingConnection;
use App\Pricing\Money;
use App\Services\Analytics\ReportingPeriod;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class GoogleAdsReport
{
    private const VERSION = 'v26';

    public function __construct(private readonly GoogleReportingClient $google) {}

    /**
     * @return array<string, mixed>
     */
    public function forPeriod(ReportingPeriod $period): array
    {
        $customerId = (string) config('services.google.ads_customer_id');

        if ($customerId === '') {
            return $this->unavailable('missing_customer', 'The Google Ads customer ID is not set.');
        }

        if (! GoogleReportingConnection::query()->exists()) {
            return $this->unavailable('disconnected', 'Connect Google Ads from this page, then sign in as the account that owns the campaigns.');
        }

        $key = 'google-ads-report:'.$customerId.':'.$period->start->toDateString().':'.$period->end->toDateString();

        $cached = Cache::get($key);

        if (is_array($cached)) {
            return $cached;
        }

        try {
            $report = $this->fetch($customerId, $period);
        } catch (Throwable $exception) {
            $report = $this->unavailable(
                $exception instanceof AdsReportException ? $exception->state : 'temporary',
                $exception->getMessage(),
            );
        }

        Cache::put($key, $report, ($report['available'] ?? false) ? now()->addHour() : now()->addMinutes(10));

        return $report;
    }

    /**
     * @return array<string, mixed>
     */
    private function fetch(string $customerId, ReportingPeriod $period): array
    {
        try {
            $token = $this->google->accessToken();
        } catch (Throwable $exception) {
            throw new AdsReportException('expired', 'The Google connection needs to be renewed. Connect Google Ads again.');
        }

        if ($token === null) {
            return $this->unavailable('disconnected', 'Connect Google Ads from this page, then sign in as the account that owns the campaigns.');
        }

        $from = $period->start->toDateString();
        $to = $period->end->toDateString();

        $customer = $this->search($token, $customerId, 'SELECT customer.currency_code, customer.descriptive_name FROM customer LIMIT 1');
        $currency = (string) ($customer[0]['customer']['currencyCode'] ?? '');
        $isPounds = $currency === 'GBP';

        $totals = $this->search($token, $customerId, <<<GAQL
            SELECT metrics.cost_micros, metrics.clicks, metrics.impressions, metrics.conversions, metrics.all_conversions, metrics.ctr, metrics.average_cpc
            FROM customer
            WHERE segments.date BETWEEN '{$from}' AND '{$to}'
            GAQL);

        $campaigns = $this->search($token, $customerId, <<<GAQL
            SELECT campaign.id, campaign.name, campaign.status, metrics.cost_micros, metrics.clicks, metrics.impressions, metrics.conversions, metrics.average_cpc
            FROM campaign
            WHERE segments.date BETWEEN '{$from}' AND '{$to}'
            AND campaign.status != 'REMOVED'
            ORDER BY metrics.cost_micros DESC
            GAQL);

        try {
            $actions = $this->search($token, $customerId, <<<GAQL
                SELECT segments.conversion_action_name, metrics.conversions
                FROM campaign
                WHERE segments.date BETWEEN '{$from}' AND '{$to}'
                GAQL);
        } catch (AdsReportException) {
            $actions = [];
        }

        $metrics = $this->sumMetrics($totals);
        $spendPence = $isPounds ? $this->microsToPence($metrics['cost']) : null;

        return [
            'available' => true,
            'state' => 'ok',
            'message' => $isPounds
                ? 'Figures are from the Google Ads API for '.$period->label().'. Dates follow the Google Ads account.'
                : 'This Google Ads account reports in '.$currency.', so costs are not shown as pounds.',
            'currency' => $currency,
            'spend_is_pounds' => $isPounds,
            'spend_pence' => $spendPence,
            'spend' => $spendPence === null ? null : Money::formatPenceExact($spendPence),
            'clicks' => $metrics['clicks'],
            'impressions' => $metrics['impressions'],
            'conversions' => $metrics['conversions'],
            'ctr' => $metrics['impressions'] > 0 ? round(($metrics['clicks'] / $metrics['impressions']) * 100, 2) : null,
            'average_cpc' => $isPounds && $metrics['clicks'] > 0
                ? Money::formatPenceExact((int) round($spendPence / $metrics['clicks']))
                : null,
            'refreshed_at' => now()->timezone(ReportingPeriod::TIMEZONE)->format('j M Y, H:i'),
            'account_url' => 'https://ads.google.com/aw/overview?ocid='.$customerId,
            'campaigns' => $this->campaignRows($campaigns, $isPounds),
            'actions' => $this->actionRows($actions),
            'diagnostics' => $this->diagnostics($token, $customerId, $from, $to, $isPounds),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function search(string $token, string $customerId, string $query): array
    {
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(20)
            ->post('https://googleads.googleapis.com/'.self::VERSION.'/customers/'.$customerId.'/googleAds:searchStream', [
                'query' => trim($query),
            ]);

        if ($response->status() === 429) {
            throw new AdsReportException('rate_limit', 'Google Ads is limiting requests. Try again in a few minutes.');
        }

        if ($response->failed()) {
            throw new AdsReportException($this->stateFrom($response), $this->messageFrom($response));
        }

        $payload = $response->json();
        $rows = [];

        foreach (is_array($payload) ? $payload : [] as $batch) {
            if (! is_array($batch)) {
                continue;
            }

            foreach ($batch['results'] ?? [] as $row) {
                if (is_array($row)) {
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return array{cost: int, clicks: int, impressions: int, conversions: float}
     */
    private function sumMetrics(array $rows): array
    {
        $cost = 0;
        $clicks = 0;
        $impressions = 0;
        $conversions = 0.0;

        foreach ($rows as $row) {
            $metrics = $row['metrics'] ?? [];
            $cost += (int) ($metrics['costMicros'] ?? 0);
            $clicks += (int) ($metrics['clicks'] ?? 0);
            $impressions += (int) ($metrics['impressions'] ?? 0);
            $conversions += (float) ($metrics['conversions'] ?? 0);
        }

        return compact('cost', 'clicks', 'impressions', 'conversions');
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function campaignRows(array $rows, bool $isPounds): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $id = (string) ($row['campaign']['id'] ?? '');

            if ($id === '') {
                continue;
            }

            $grouped[$id] ??= [
                'id' => $id,
                'name' => (string) ($row['campaign']['name'] ?? 'Campaign'),
                'status' => ucfirst(strtolower((string) ($row['campaign']['status'] ?? ''))),
                'cost' => 0,
                'clicks' => 0,
                'impressions' => 0,
                'conversions' => 0.0,
            ];
            $metrics = $row['metrics'] ?? [];
            $grouped[$id]['cost'] += (int) ($metrics['costMicros'] ?? 0);
            $grouped[$id]['clicks'] += (int) ($metrics['clicks'] ?? 0);
            $grouped[$id]['impressions'] += (int) ($metrics['impressions'] ?? 0);
            $grouped[$id]['conversions'] += (float) ($metrics['conversions'] ?? 0);
        }

        return array_values(array_map(function (array $campaign) use ($isPounds): array {
            $pence = $isPounds ? $this->microsToPence($campaign['cost']) : null;
            $conversions = $campaign['conversions'];

            return [
                'id' => $campaign['id'],
                'name' => $campaign['name'],
                'status' => $campaign['status'],
                'spend' => $pence === null ? null : Money::formatPenceExact($pence),
                'clicks' => $campaign['clicks'],
                'conversions' => $this->formatConversions($conversions),
                'cost_per_conversion' => $pence !== null && $conversions > 0
                    ? Money::formatPenceExact((int) round($pence / $conversions))
                    : null,
            ];
        }, $grouped));
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    private function actionRows(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $name = (string) ($row['segments']['conversionActionName'] ?? '');

            if ($name === '') {
                continue;
            }

            $grouped[$name] = ($grouped[$name] ?? 0) + (float) ($row['metrics']['conversions'] ?? 0);
        }

        $actions = [];

        foreach ($grouped as $name => $conversions) {
            if ($conversions <= 0) {
                continue;
            }

            [$kind, $review] = $this->classifyAction($name);
            $actions[] = [
                'name' => $name,
                'kind' => $kind,
                'conversions' => $this->formatConversions($conversions),
                'review' => $review,
            ];
        }

        return $actions;
    }

    /**
     * @return array{0: string, 1: bool}
     */
    private function classifyAction(string $name): array
    {
        $lower = strtolower($name);

        return match (true) {
            str_contains($lower, 'whatsapp') => ['WhatsApp click', false],
            str_contains($lower, 'call'), str_contains($lower, 'phone') => ['Call click', false],
            str_contains($lower, 'book') => ['Imported booked job', false],
            str_contains($lower, 'page'), str_contains($lower, 'view') => ['Page view', true],
            str_contains($lower, 'quote'), str_contains($lower, 'lead'), str_contains($lower, 'form') => ['Quote or form', false],
            default => ['Other action', true],
        };
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function diagnostics(string $token, string $customerId, string $from, string $to, bool $isPounds): array
    {
        try {
            $rows = $this->search($token, $customerId, <<<GAQL
                SELECT search_term_view.search_term, metrics.cost_micros, metrics.clicks, metrics.conversions
                FROM search_term_view
                WHERE segments.date BETWEEN '{$from}' AND '{$to}'
                AND metrics.clicks >= 5
                AND metrics.conversions = 0
                ORDER BY metrics.cost_micros DESC
                LIMIT 8
                GAQL);
        } catch (Throwable) {
            return [];
        }

        return array_values(array_filter(array_map(function (array $row) use ($isPounds): ?array {
            $term = (string) ($row['searchTermView']['searchTerm'] ?? '');

            if ($term === '') {
                return null;
            }

            $pence = $isPounds ? $this->microsToPence((int) ($row['metrics']['costMicros'] ?? 0)) : null;

            return [
                'term' => $term,
                'clicks' => (int) ($row['metrics']['clicks'] ?? 0),
                'spend' => $pence === null ? null : Money::formatPenceExact($pence),
            ];
        }, $rows)));
    }

    private function microsToPence(int $micros): int
    {
        return (int) round($micros / 10_000);
    }

    private function formatConversions(float $conversions): string
    {
        return rtrim(rtrim(number_format($conversions, 2, '.', ''), '0'), '.');
    }

    private function stateFrom(Response $response): string
    {
        $encoded = json_encode($response->json()) ?: '';

        return match (true) {
            str_contains($encoded, 'CLOUD_PROJECT_NOT_APPROVED_FOR_PRODUCTION') => 'not_approved',
            $response->status() === 401 => 'expired',
            $response->status() === 403 => 'permission',
            default => 'temporary',
        };
    }

    private function messageFrom(Response $response): string
    {
        $state = $this->stateFrom($response);

        return match ($state) {
            'not_approved' => 'The Google Cloud project ng-home-cleaners-integrations can sign in, but Google has not approved it for production Ads reporting. Apply for Explorer or Basic access on the Google Ads API overview for that project.',
            'expired' => 'The Google connection needs to be renewed. Connect Google Ads again.',
            'permission' => 'This Google account cannot read the Ads customer. Sign in as the account that owns 339-181-7713.',
            default => 'Google Ads did not return a report. Try again shortly.',
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function unavailable(string $state, string $message): array
    {
        return [
            'available' => false,
            'state' => $state,
            'message' => $message,
            'currency' => null,
            'spend_is_pounds' => false,
            'spend_pence' => null,
            'spend' => null,
            'clicks' => null,
            'impressions' => null,
            'conversions' => null,
            'ctr' => null,
            'average_cpc' => null,
            'refreshed_at' => null,
            'account_url' => 'https://ads.google.com',
            'campaigns' => [],
            'actions' => [],
            'diagnostics' => [],
        ];
    }
}

final class AdsReportException extends RuntimeException
{
    public function __construct(public readonly string $state, string $message)
    {
        parent::__construct($message);
    }
}
