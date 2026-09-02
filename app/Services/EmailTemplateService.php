<?php

namespace App\Services;

use App\Enums\ArrivalWindow;
use App\Enums\EmailTemplateKey;
use App\Models\Booking;
use App\Models\EmailTemplate;
use App\Models\QuoteRequest;
use App\Models\SiteSetting;
use Illuminate\Support\Str;

class EmailTemplateService
{
    public function ensureDefaults(): void
    {
        foreach (EmailTemplateKey::cases() as $key) {
            EmailTemplate::query()->firstOrCreate(
                ['key' => $key->value],
                $this->defaultAttributes($key),
            );
        }
    }

    public function template(EmailTemplateKey $key): EmailTemplate
    {
        $template = EmailTemplate::query()->where('key', $key->value)->first();

        if ($template) {
            return $template;
        }

        return EmailTemplate::query()->create([
            'key' => $key->value,
            ...$this->defaultAttributes($key),
        ]);
    }

    /**
     * @return array{name: string, description: string, subject: string, heading: string, body: string}
     */
    public function defaultAttributes(EmailTemplateKey $key): array
    {
        return match ($key) {
            EmailTemplateKey::CustomerQuoteAcknowledgement => [
                'name' => $key->label(),
                'description' => $key->description(),
                'subject' => 'We received your estimate request ({{reference}})',
                'heading' => 'We received your estimate request',
                'body' => <<<'BODY'
Hello {{first_name}},

Thank you for requesting an estimate from {{business_name}}.

**Your reference:** {{reference}}

**Service:** {{service_name}}

**Preferred visit:** {{preferred_date}}

**Guide estimate:** {{guide_estimate}}

This guide price helps you plan ahead. It is not yet your final confirmed quote. We will check your details and send a fixed price in writing within one working day.

If anything needs correcting, reply to this email or call us on {{business_phone}} and quote reference **{{reference}}**.

Thanks,
{{business_name}}
BODY,
            ],
            EmailTemplateKey::InternalQuoteRequest => [
                'name' => $key->label(),
                'description' => $key->description(),
                'subject' => 'New estimate request {{reference}}',
                'heading' => 'New estimate request {{reference}}',
                'body' => <<<'BODY'
A new estimate request has arrived from **{{source}}**.

**Customer:** {{full_name}}
**Phone:** {{phone}}
**Email:** {{email}}
**Postcode:** {{postcode}}
**Service:** {{service_name}}
**Guide estimate:** {{guide_estimate}}

Full lead details are included below.
BODY,
            ],
            EmailTemplateKey::CustomerFinalQuote => [
                'name' => $key->label(),
                'description' => $key->description(),
                'subject' => 'Your quote from {{business_name}} ({{reference}})',
                'heading' => 'Your confirmed quote',
                'body' => <<<'BODY'
Hello {{first_name}},

Thank you for your patience. Here is your confirmed quote from {{business_name}}.

**Your reference:** {{reference}}

**Service:** {{service_name}}

**Preferred visit:** {{preferred_date}}

**Confirmed quote:** {{final_quote}}

**Original guide estimate:** {{guide_estimate}}

This is our fixed price based on the details you provided. Reply to this email or call us on {{business_phone}} if you would like to go ahead or need anything changed.

Thanks,
{{business_name}}
BODY,
            ],
            EmailTemplateKey::CustomerQuoteFollowUp => [
                'name' => $key->label(),
                'description' => $key->description(),
                'subject' => 'We are preparing your quote ({{reference}})',
                'heading' => 'We are preparing your quote',
                'body' => <<<'BODY'
Hello {{first_name}},

Just a short note to say we have your estimate request **{{reference}}** and are preparing your fixed price.

We will send that in writing as soon as we have checked the details. WhatsApp is still the quickest way to add a walkthrough video or ask a question in the meantime.

Thanks,
{{business_name}}
BODY,
            ],
            EmailTemplateKey::CustomerReviewRequest => [
                'name' => $key->label(),
                'description' => $key->description(),
                'subject' => 'How did we do? ({{booking_reference}})',
                'heading' => 'Thank you for having us',
                'body' => <<<'BODY'
Hello {{first_name}},

Your booking **{{booking_reference}}** is complete. If the clean was up to standard, a short Google review helps other Nottingham households find us.

Thank you for choosing {{business_name}}.

Thanks,
{{business_name}}
BODY,
            ],
        };
    }

    /**
     * @param  array<string, string|null>  $variables
     */
    public function replace(string $content, array $variables): string
    {
        return (string) preg_replace_callback(
            '/\{\{\s*([a-z0-9_]+)\s*\}\}/i',
            function (array $matches) use ($variables): string {
                $key = strtolower($matches[1]);

                return (string) ($variables[$key] ?? '');
            },
            $content,
        );
    }

    /**
     * @param  array<string, string|null>  $variables
     */
    public function renderBodyHtml(string $body, array $variables): string
    {
        $replaced = $this->replace($body, $variables);

        return (string) Str::markdown($replaced, [
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);
    }

    /**
     * @return array<string, string|null>
     */
    public function variablesForQuoteRequest(QuoteRequest $quoteRequest, ?SiteSetting $settings = null): array
    {
        $settings ??= SiteSetting::instance();

        $preferredVisit = collect([
            $quoteRequest->preferred_date?->format('l j F Y'),
            ArrivalWindow::tryFrom((string) $quoteRequest->arrival_window)?->label(),
        ])->filter()->implode(' · ');

        return [
            'first_name' => $quoteRequest->first_name,
            'last_name' => $quoteRequest->last_name,
            'full_name' => $quoteRequest->fullName(),
            'reference' => $quoteRequest->reference,
            'source' => $quoteRequest->source?->label(),
            'phone' => $quoteRequest->phone,
            'business_phone' => $settings->phoneDisplay(),
            'email' => $quoteRequest->email,
            'postcode' => $quoteRequest->postcode,
            'service_name' => $quoteRequest->service?->name,
            'preferred_date' => $preferredVisit !== '' ? $preferredVisit : null,
            'arrival_window' => ArrivalWindow::tryFrom((string) $quoteRequest->arrival_window)?->label(),
            'guide_estimate' => $quoteRequest->guide_estimate_headline,
            'final_quote' => $quoteRequest->finalQuoteDisplay(),
            'business_name' => $settings->business_name ?? config('app.name'),
            'whatsapp_url' => $settings->whatsappLink(),
        ];
    }

    /**
     * @return array<string, string|null>
     */
    public function variablesForBooking(Booking $booking, ?SiteSetting $settings = null): array
    {
        $settings ??= SiteSetting::instance();
        $customer = $booking->customer;

        return [
            'first_name' => $customer?->first_name ?? 'there',
            'full_name' => $customer?->fullName(),
            'booking_reference' => $booking->reference,
            'business_name' => $settings->business_name ?? config('app.name'),
            'google_review_url' => $settings->google_business_url,
            'business_phone' => $settings->phoneDisplay(),
        ];
    }
}
