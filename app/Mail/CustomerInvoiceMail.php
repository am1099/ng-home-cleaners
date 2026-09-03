<?php

namespace App\Mail;

use App\Enums\EmailTemplateKey;
use App\Models\EmailTemplate;
use App\Models\Invoice;
use App\Models\SiteSetting;
use App\Services\EmailTemplateService;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class CustomerInvoiceMail extends Mailable
{
    public function __construct(
        public Invoice $invoice,
        public string $pdfContents,
        public ?string $subjectOverride = null,
        public ?string $bodyOverride = null,
    ) {}

    public function envelope(): Envelope
    {
        $template = EmailTemplate::for(EmailTemplateKey::CustomerInvoice);

        return new Envelope(
            subject: filled($this->subjectOverride)
                ? (string) $this->subjectOverride
                : $template->renderSubject($this->variables()),
        );
    }

    public function content(): Content
    {
        $settings = SiteSetting::instance();
        $template = EmailTemplate::for(EmailTemplateKey::CustomerInvoice);
        $variables = $this->variables($settings);

        $bodyHtml = filled($this->bodyOverride)
            ? nl2br(e($this->bodyOverride))
            : $template->renderBodyHtml($variables);

        return new Content(
            markdown: 'mail.invoices.customer-invoice',
            with: [
                'settings' => $settings,
                'invoice' => $this->invoice,
                'heading' => $template->renderHeading($variables),
                'bodyHtml' => $bodyHtml,
            ],
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $filename = ($this->invoice->invoice_number ?: 'invoice').'.pdf';

        return [
            Attachment::fromData(fn (): string => $this->pdfContents, $filename)
                ->withMime('application/pdf'),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    private function variables(?SiteSetting $settings = null): array
    {
        return app(EmailTemplateService::class)
            ->variablesForInvoice($this->invoice, $settings);
    }
}
