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

class CustomerFinalQuoteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your quote from '.(SiteSetting::instance()->business_name ?? config('app.name')).' ('.$this->quoteRequest->reference.')',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.quote-requests.customer-final-quote',
            with: [
                'settings' => SiteSetting::instance(),
            ],
        );
    }
}
