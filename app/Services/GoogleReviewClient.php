<?php

namespace App\Services;

use App\Models\GoogleBusinessConnection;
use App\Models\Testimonial;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleReviewClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }

    public function isConnected(): bool
    {
        return GoogleBusinessConnection::current() !== null;
    }

    public function authorizationUrl(string $state): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => config('services.google.redirect'),
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/business.manage',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function connect(string $code): GoogleBusinessConnection
    {
        $token = $this->tokenRequest([
            'code' => $code,
            'grant_type' => 'authorization_code',
            'redirect_uri' => config('services.google.redirect'),
        ]);

        $accessToken = $token['access_token'] ?? null;
        $refreshToken = $token['refresh_token'] ?? null;

        if (! is_string($accessToken) || ! is_string($refreshToken)) {
            throw new RuntimeException('Google did not return a refresh token. Disconnect the app in your Google account and connect again.');
        }

        try {
            $account = $this->firstAccount($accessToken);
            $location = $this->firstLocation($accessToken, $account['name']);
        } catch (RequestException $exception) {
            throw new RuntimeException($this->googleMessage($exception), previous: $exception);
        }

        $attributes = [
            'refresh_token' => $refreshToken,
            'account_name' => $account['accountName'] ?? null,
            'account_resource' => $account['name'],
            'location_title' => $location['title'] ?? null,
            'location_resource' => $this->locationResource($location['name']),
            'connected_at' => now(),
        ];

        $existing = GoogleBusinessConnection::current();

        if ($existing) {
            $existing->update($attributes);

            return $existing->refresh();
        }

        return GoogleBusinessConnection::query()->create($attributes);
    }

    /**
     * @return array{created: int, updated: int, skipped: int}
     */
    public function sync(): array
    {
        $connection = GoogleBusinessConnection::current();

        if (! $connection) {
            throw new RuntimeException('Connect a Google Business Profile before syncing reviews.');
        }

        $accessToken = $this->refreshAccessToken($connection);
        $created = 0;
        $updated = 0;
        $skipped = 0;
        $pageToken = null;
        $sourceUrl = app(SiteSettingsService::class)->get()->google_business_url;

        do {
            $query = ['pageSize' => 50];
            if ($pageToken) {
                $query['pageToken'] = $pageToken;
            }

            try {
                $response = Http::withToken($accessToken)
                    ->acceptJson()
                    ->get('https://mybusiness.googleapis.com/v4/'.$connection->account_resource.'/'.$connection->location_resource.'/reviews', $query)
                    ->throw()
                    ->json();
            } catch (RequestException $exception) {
                throw new RuntimeException($this->googleMessage($exception), previous: $exception);
            }

            foreach ($response['reviews'] ?? [] as $review) {
                $result = $this->storeReview($review, $sourceUrl);
                $created += $result === 'created' ? 1 : 0;
                $updated += $result === 'updated' ? 1 : 0;
                $skipped += $result === 'skipped' ? 1 : 0;
            }

            $pageToken = $response['nextPageToken'] ?? null;
        } while (is_string($pageToken) && $pageToken !== '');

        return compact('created', 'updated', 'skipped');
    }

    public function disconnect(): void
    {
        GoogleBusinessConnection::query()->delete();
    }

    /**
     * Profile views and actions from Google Business Profile.
     * Age and gender are not available from this connection.
     *
     * @return array{available: bool, message: string, metrics: array<string, int>}
     */
    public function performanceSince(Carbon $since): array
    {
        $connection = GoogleBusinessConnection::current();

        if (! $connection) {
            return [
                'available' => false,
                'message' => 'Connect Google Business Profile from Reviews to pull profile views and calls.',
                'metrics' => [],
            ];
        }

        $accessToken = $this->refreshAccessToken($connection);
        $location = str_contains($connection->location_resource, 'locations/')
            ? $connection->location_resource
            : 'locations/'.$connection->location_resource;

        $metrics = [
            'Website clicks' => 'WEBSITE_CLICKS',
            'Calls' => 'CALL_CLICKS',
            'Direction requests' => 'BUSINESS_DIRECTION_REQUESTS',
        ];
        $totals = [];

        foreach ($metrics as $label => $metric) {
            $response = Http::withToken($accessToken)
                ->acceptJson()
                ->get('https://businessprofileperformance.googleapis.com/v1/'.$location.':getDailyMetricsTimeSeries', [
                    'dailyMetric' => $metric,
                    'dailyRange.startDate.year' => $since->year,
                    'dailyRange.startDate.month' => $since->month,
                    'dailyRange.startDate.day' => $since->day,
                    'dailyRange.endDate.year' => now()->year,
                    'dailyRange.endDate.month' => now()->month,
                    'dailyRange.endDate.day' => now()->day,
                ])
                ->throw()
                ->json();

            $sum = 0;

            foreach ($response['timeSeries']['datedValues'] ?? [] as $point) {
                $sum += (int) ($point['value'] ?? 0);
            }

            $totals[$label] = $sum;
        }

        return [
            'available' => true,
            'message' => $connection->location_title ?: 'Google Business Profile',
            'metrics' => $totals,
        ];
    }

    /**
     * @param  array<string, mixed>  $review
     */
    private function storeReview(array $review, ?string $sourceUrl): string
    {
        $comment = $this->originalComment($review['comment'] ?? null);
        $reviewId = $review['reviewId'] ?? null;
        $rating = $this->stars($review['starRating'] ?? null);
        $name = $review['reviewer']['displayName'] ?? null;

        if (! is_string($reviewId) || ! is_string($name) || $rating === null || $comment === null) {
            return 'skipped';
        }

        $existing = Testimonial::query()->where('google_review_id', $reviewId)->first();
        $reviewedAt = isset($review['createTime']) ? Carbon::parse($review['createTime']) : null;

        if ($existing) {
            $existing->update([
                'customer_name' => $name,
                'rating' => $rating,
                'review' => $comment,
                'reviewed_at' => $reviewedAt,
                'source' => 'Google',
                'source_url' => $sourceUrl,
            ]);

            return 'updated';
        }

        Testimonial::query()->create([
            'customer_name' => $name,
            'rating' => $rating,
            'review' => $comment,
            'reviewed_at' => $reviewedAt,
            'source' => 'Google',
            'source_url' => $sourceUrl,
            'google_review_id' => $reviewId,
            'is_published' => true,
            'published_at' => now(),
            'is_demo' => false,
            'sort_order' => 0,
        ]);

        return 'created';
    }

    private function originalComment(mixed $comment): ?string
    {
        if (! is_string($comment)) {
            return null;
        }

        $comment = trim(explode('(Translated by Google)', $comment)[0]);

        return $comment === '' ? null : $comment;
    }

    private function stars(mixed $rating): ?int
    {
        return match ($rating) {
            'ONE' => 1,
            'TWO' => 2,
            'THREE' => 3,
            'FOUR' => 4,
            'FIVE' => 5,
            default => null,
        };
    }

    private function refreshAccessToken(GoogleBusinessConnection $connection): string
    {
        $token = $this->tokenRequest([
            'refresh_token' => $connection->refresh_token,
            'grant_type' => 'refresh_token',
        ]);

        if (! is_string($token['access_token'] ?? null)) {
            throw new RuntimeException('Google did not refresh the connection. Connect the account again.');
        }

        return $token['access_token'];
    }

    /**
     * @param  array<string, string>  $fields
     * @return array<string, mixed>
     */
    private function tokenRequest(array $fields): array
    {
        try {
            return Http::asForm()
                ->acceptJson()
                ->post('https://oauth2.googleapis.com/token', $fields + [
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            throw new RuntimeException($this->googleMessage($exception), previous: $exception);
        }
    }

    /**
     * @return array{name: string, accountName?: string}
     */
    private function firstAccount(string $accessToken): array
    {
        $accounts = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://mybusinessaccountmanagement.googleapis.com/v1/accounts')
            ->throw()
            ->json('accounts', []);

        $account = $accounts[0] ?? null;

        if (! is_array($account) || ! is_string($account['name'] ?? null)) {
            throw new RuntimeException('No Google Business Profile account was found for that login.');
        }

        return $account;
    }

    /**
     * @return array{name: string, title?: string}
     */
    private function firstLocation(string $accessToken, string $accountResource): array
    {
        $locations = Http::withToken($accessToken)
            ->acceptJson()
            ->get('https://mybusinessbusinessinformation.googleapis.com/v1/'.$accountResource.'/locations', [
                'readMask' => 'name,title',
                'pageSize' => 1,
            ])
            ->throw()
            ->json('locations', []);

        $location = $locations[0] ?? null;

        if (! is_array($location) || ! is_string($location['name'] ?? null)) {
            throw new RuntimeException('No business location was found on that Google account.');
        }

        return $location;
    }

    private function locationResource(string $name): string
    {
        if (str_contains($name, 'locations/')) {
            return 'locations/'.Str::afterLast($name, 'locations/');
        }

        return $name;
    }

    private function googleMessage(RequestException $exception): string
    {
        $message = $exception->response?->json('error_description')
            ?? $exception->response?->json('error.message')
            ?? $exception->response?->json('error');

        return is_string($message) && $message !== ''
            ? $message
            : 'Google could not complete that request.';
    }
}
