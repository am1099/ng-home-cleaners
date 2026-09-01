<?php

namespace App\Actions;

use App\Jobs\SendReviewRequestJob;
use App\Models\Booking;

class SendReviewRequest
{
    public function handle(Booking $booking): void
    {
        SendReviewRequestJob::dispatch($booking->id);
    }
}
