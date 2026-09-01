<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Mail\CustomerReviewRequestMail;
use App\Models\Booking;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendReviewRequestJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $bookingId,
    ) {}

    public function handle(): void
    {
        $booking = Booking::query()->with('customer')->find($this->bookingId);

        if (! $booking || $booking->status !== BookingStatus::Completed) {
            return;
        }

        if ($booking->review_request_sent_at) {
            return;
        }

        $email = $booking->customer?->email;

        if (! filled($email)) {
            return;
        }

        Mail::to($email)->queue(new CustomerReviewRequestMail($booking));

        $booking->forceFill([
            'review_request_sent_at' => now(),
        ])->save();
    }
}
