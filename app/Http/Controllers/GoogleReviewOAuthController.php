<?php

namespace App\Http\Controllers;

use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Services\GoogleReviewClient;
use Filament\Notifications\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class GoogleReviewOAuthController extends Controller
{
    public function connect(GoogleReviewClient $google): RedirectResponse
    {
        abort_unless($google->isConfigured(), 404);

        $state = Str::random(40);
        session(['google_review_oauth_state' => $state]);

        return redirect()->away($google->authorizationUrl($state));
    }

    public function callback(Request $request, GoogleReviewClient $google): RedirectResponse
    {
        abort_unless($google->isConfigured(), 404);

        $expected = session()->pull('google_review_oauth_state');

        if (! is_string($expected) || ! hash_equals($expected, (string) $request->query('state'))) {
            return $this->back('The Google connection was interrupted. Try again.');
        }

        if ($request->filled('error')) {
            return $this->back('Google did not connect the account.');
        }

        try {
            $connection = $google->connect((string) $request->query('code'));
        } catch (RuntimeException $exception) {
            return $this->back($exception->getMessage());
        }

        $place = $connection->location_title ?: 'your Google Business Profile';

        Notification::make()
            ->title('Google connected')
            ->body('Reviews can now be synced from '.$place.'.')
            ->success()
            ->send();

        return redirect()->to(TestimonialResource::getUrl('index'));
    }

    private function back(string $message): RedirectResponse
    {
        Notification::make()
            ->title('Google reviews')
            ->body($message)
            ->danger()
            ->send();

        return redirect()->to(TestimonialResource::getUrl('index'));
    }
}
