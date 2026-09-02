<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InternalQuoteRequestMail extends Mailable
{
    public function __construct(
        public QuoteRequest $quoteRequest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New estimate request '.$this->quoteRequest->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.quote-requests.internal',
        );
    }
}
