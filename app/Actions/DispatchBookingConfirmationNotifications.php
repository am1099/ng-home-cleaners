<?php

namespace App\Actions;

use App\Mail\InternalBookingConfirmationMail;
use App\Models\BookingConfirmation;
use App\Models\SiteSetting;
use App\Models\User;
use App\Notifications\NewBookingConfirmationNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class DispatchBookingConfirmationNotifications
{
    public function handle(BookingConfirmation $confirmation): void
    {
        try {
            User::query()->each(
                fn (User $user) => $user->notify(new NewBookingConfirmationNotification($confirmation)),
            );
        } catch (Throwable $exception) {
            Log::error('Booking confirmation database notification failed.', [
                'confirmation_id' => $confirmation->id,
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
                Mail::to($recipient)->send(new InternalBookingConfirmationMail($confirmation));
            } catch (Throwable $exception) {
                Log::error('Booking confirmation email failed.', [
                    'confirmation_id' => $confirmation->id,
                    'to' => $recipient,
                    'message' => $exception->getMessage(),
                ]);
                report($exception);
            }
        }
    }
}
