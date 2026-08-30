<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerQuoteAcknowledgementMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'We received your estimate request ('.$this->quoteRequest->reference.')',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.quote-requests.customer-acknowledgement',
            with: [
                'settings' => SiteSetting::instance(),
            ],
        );
    }
}
