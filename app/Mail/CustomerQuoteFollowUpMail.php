<?php

namespace App\Mail;

use App\Enums\EmailTemplateKey;
use App\Models\EmailTemplate;
use App\Models\QuoteRequest;
use App\Models\SiteSetting;
use App\Services\EmailTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerQuoteFollowUpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $quoteRequest,
    ) {}

    public function envelope(): Envelope
    {
        $template = EmailTemplate::for(EmailTemplateKey::CustomerQuoteFollowUp);

        return new Envelope(
            subject: $template->renderSubject($this->variables()),
        );
    }

    public function content(): Content
    {
        $settings = SiteSetting::instance();
        $template = EmailTemplate::for(EmailTemplateKey::CustomerQuoteFollowUp);
        $variables = $this->variables($settings);

        return new Content(
            markdown: 'mail.quote-requests.customer-follow-up',
            with: [
                'settings' => $settings,
                'heading' => $template->renderHeading($variables),
                'bodyHtml' => $template->renderBodyHtml($variables),
            ],
        );
    }

    /**
     * @return array<string, string|null>
     */
    private function variables(?SiteSetting $settings = null): array
    {
        return app(EmailTemplateService::class)
            ->variablesForQuoteRequest($this->quoteRequest, $settings);
    }
}
