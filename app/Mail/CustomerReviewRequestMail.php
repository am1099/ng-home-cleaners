<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\SiteSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerReviewRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'How did we do? ('.$this->booking->reference.')',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.bookings.review-request',
            with: [
                'settings' => SiteSetting::instance(),
            ],
        );
    }
}
