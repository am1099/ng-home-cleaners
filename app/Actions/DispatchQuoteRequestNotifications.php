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
    /**
     * @return array{sent: list<string>, failed: list<array{to: string, error: string}>}
     */
    public function handle(QuoteRequest $quoteRequest): array
    {
        $sent = [];
        $failed = [];

        try {
            User::query()->each(
                fn (User $user) => $user->notify(new NewQuoteRequestNotification($quoteRequest)),
            );
        } catch (Throwable $exception) {
            Log::error('Quote database notification failed.', [
                'reference' => $quoteRequest->reference,
                'message' => $exception->getMessage(),
            ]);
            report($exception);
        }

        $settings = SiteSetting::instance();
        $recipients = array_values(array_unique(array_filter(
            $settings->lead_notification_emails ?? [$settings->email],
        )));

        if ($recipients === []) {
            $recipients = array_filter([(string) $settings->email]);
        }

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient)->send(new InternalQuoteRequestMail($quoteRequest));
                $sent[] = $recipient;
            } catch (Throwable $exception) {
                $failed[] = [
                    'to' => $recipient,
                    'error' => $exception->getMessage(),
                ];

                Log::error('Internal quote email failed.', [
                    'reference' => $quoteRequest->reference,
                    'to' => $recipient,
                    'message' => $exception->getMessage(),
                    'mailer' => config('mail.default'),
                    'from' => config('mail.from.address'),
                ]);
                report($exception);
            }
        }

        if (filled($quoteRequest->email)) {
            try {
                Mail::to($quoteRequest->email)->send(new CustomerQuoteAcknowledgementMail($quoteRequest));
                $sent[] = (string) $quoteRequest->email;
            } catch (Throwable $exception) {
                $failed[] = [
                    'to' => (string) $quoteRequest->email,
                    'error' => $exception->getMessage(),
                ];

                Log::error('Customer quote acknowledgement failed.', [
                    'reference' => $quoteRequest->reference,
                    'to' => $quoteRequest->email,
                    'message' => $exception->getMessage(),
                    'mailer' => config('mail.default'),
                    'from' => config('mail.from.address'),
                ]);
                report($exception);
            }
        }

        return compact('sent', 'failed');
    }
}
