<?php

namespace App\Http\Controllers;

use App\Filament\Pages\Analytics;
use App\Services\GoogleReportingClient;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleReportingOAuthController extends Controller
{
    public function connect(GoogleReportingClient $google): RedirectResponse
    {
        abort_unless($google->isConfigured(), 404);

        $state = Str::random(40);
        session(['google_reporting_oauth_state' => $state]);

        return redirect()->away($google->authorizationUrl($state));
    }

    public function callback(Request $request, GoogleReportingClient $google): RedirectResponse
    {
        abort_unless($google->isConfigured(), 404);

        $expected = session()->pull('google_reporting_oauth_state');

        if (! is_string($expected) || ! hash_equals($expected, (string) $request->query('state'))) {
            return $this->back('The Google connection was interrupted. Try again.');
        }

        if ($request->filled('error')) {
            return $this->back('Google did not connect Ads and Analytics.');
        }

        try {
            $google->connect((string) $request->query('code'));
        } catch (RuntimeException $exception) {
            return $this->back($exception->getMessage());
        }

        Notification::make()
            ->title('Google Ads and Analytics connected')
            ->success()
            ->send();

        return redirect()->to(Analytics::getUrl());
    }

    private function back(string $message): RedirectResponse
    {
        Notification::make()
            ->title('Google reporting')
            ->body($message)
            ->danger()
            ->send();

        return redirect()->to(Analytics::getUrl());
    }
}
