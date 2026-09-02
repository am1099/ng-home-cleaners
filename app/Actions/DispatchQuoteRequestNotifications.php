<?php

namespace App\Actions;

use App\Mail\CustomerQuoteAcknowledgementMail;
use App\Mail\InternalQuoteRequestMail;
use App\Models\QuoteRequest;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\NewQuoteRequestNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DispatchQuoteRequestNotifications
{
    public function handle(QuoteRequest $quoteRequest): void
    {
        try {
            User::query()->each(
                fn (User $user) => $user->notify(new NewQuoteRequestNotification($quoteRequest)),
            );

            $settings = SiteSetting::instance();
            $recipients = array_values(array_filter($settings->lead_notification_emails ?? [$settings->email]));

            if ($recipients === []) {
                $recipients = array_filter([(string) $settings->email]);
            }

            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new InternalQuoteRequestMail($quoteRequest));
            }

            if (filled($quoteRequest->email)) {
                Mail::to($quoteRequest->email)->send(new CustomerQuoteAcknowledgementMail($quoteRequest));
            }
        } catch (Throwable $exception) {
            Log::error('Quote notification dispatch failed.', [
                'reference' => $quoteRequest->reference,
                'message' => $exception->getMessage(),
                'mailer' => config('mail.default'),
                'from' => config('mail.from.address'),
            ]);
            report($exception);
        }
    }
}
