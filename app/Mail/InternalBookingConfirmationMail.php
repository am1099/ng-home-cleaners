<?php

namespace App\Mail;

use App\Models\BookingConfirmation;
use App\Models\SiteSetting;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class InternalBookingConfirmationMail extends Mailable
{
    public function __construct(
        public BookingConfirmation $confirmation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking confirmation from '.$this->confirmation->full_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.booking-confirmations.internal',
            with: [
                'settings' => SiteSetting::instance(),
                'confirmation' => $this->confirmation,
            ],
        );
    }
}
