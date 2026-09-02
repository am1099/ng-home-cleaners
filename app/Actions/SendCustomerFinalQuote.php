<?php

namespace App\Actions;

use App\Enums\QuoteRequestStatus;
use App\Mail\CustomerFinalQuoteMail;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use Throwable;

class SendCustomerFinalQuote
{
    public function handle(QuoteRequest $quoteRequest, bool $markQuoteSent = true): void
    {
        if ($quoteRequest->final_quote_amount_pence === null) {
            throw ValidationException::withMessages([
                'final_quote_amount_pence' => 'Add a final quoted amount before sending the quote email.',
            ]);
        }

        if (blank($quoteRequest->email)) {
            throw ValidationException::withMessages([
                'email' => 'This lead has no email address on file.',
            ]);
        }

        try {
            Mail::to($quoteRequest->email)->send(new CustomerFinalQuoteMail($quoteRequest));

            if ($markQuoteSent && ! in_array($quoteRequest->status, [
                QuoteRequestStatus::QuoteSent,
                QuoteRequestStatus::Won,
                QuoteRequestStatus::Lost,
            ], true)) {
                $quoteRequest->markStatus(QuoteRequestStatus::QuoteSent);
            }
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Final quote email dispatch failed.', [
                'reference' => $quoteRequest->reference,
                'message' => $exception->getMessage(),
                'mailer' => config('mail.default'),
                'from' => config('mail.from.address'),
            ]);

            report($exception);

            throw ValidationException::withMessages([
                'email' => 'We could not send the quote email just now. Please try again.',
            ]);
        }
    }
}
