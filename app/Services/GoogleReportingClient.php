<?php

namespace App\Services;

use App\Models\GoogleReportingConnection;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GoogleReportingClient
{
    public function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled($this->redirectUri());
    }

    public function redirectUri(): string
    {
        return (string) config('services.google.reporting_redirect');
    }

    public function authorizationUrl(string $state): string
    {
        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.google.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'response_type' => 'code',
            'scope' => implode(' ', [
                'https://www.googleapis.com/auth/adwords',
                'https://www.googleapis.com/auth/analytics.readonly',
            ]),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);
    }

    public function connect(string $code): GoogleReportingConnection
    {
        try {
            $token = Http::asForm()
                ->acceptJson()
                ->post('https://oauth2.googleapis.com/token', [
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $this->redirectUri(),
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                ])
                ->throw()
                ->json();
        } catch (RequestException $exception) {
            $message = $exception->response?->json('error_description')
                ?? $exception->response?->json('error');

            throw new RuntimeException(is_string($message) && $message !== ''
                ? $message
                : 'Google did not connect Ads and Analytics.', previous: $exception);
        }

        $refreshToken = $token['refresh_token'] ?? null;

        if (! is_string($refreshToken)) {
            throw new RuntimeException('Google did not return a refresh token. Remove the app from the Google account and connect again.');
        }

        $existing = GoogleReportingConnection::query()->latest('id')->first();
        $attributes = [
            'refresh_token' => $refreshToken,
            'connected_at' => now(),
        ];

        if ($existing) {
            $existing->update($attributes);

            return $existing->refresh();
        }

        return GoogleReportingConnection::query()->create($attributes);
    }

    /**
     * A short-lived access token for the connected Google account.
     * Null when nobody has connected Ads and Analytics yet.
     */
    public function accessToken(): ?string
    {
        $connection = GoogleReportingConnection::query()->latest('id')->first();

        if (! $connection) {
            return null;
        }

        $token = Http::asForm()
            ->acceptJson()
            ->post('https://oauth2.googleapis.com/token', [
                'refresh_token' => $connection->refresh_token,
                'grant_type' => 'refresh_token',
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
            ])
            ->throw()
            ->json();

        return is_string($token['access_token'] ?? null) ? $token['access_token'] : null;
    }
}
