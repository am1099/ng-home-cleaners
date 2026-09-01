<?php

namespace App\Jobs;

use App\Enums\QuoteRequestStatus;
use App\Mail\CustomerQuoteFollowUpMail;
use App\Models\QuoteRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendQuoteFollowUpJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        QuoteRequest::query()
            ->where('status', QuoteRequestStatus::New)
            ->whereNull('follow_up_sent_at')
            ->where('submitted_at', '<=', now()->subDay())
            ->each(function (QuoteRequest $quoteRequest): void {
                Mail::to($quoteRequest->email)->queue(new CustomerQuoteFollowUpMail($quoteRequest));

                $quoteRequest->forceFill([
                    'follow_up_sent_at' => now(),
                ])->save();
            });
    }
}
