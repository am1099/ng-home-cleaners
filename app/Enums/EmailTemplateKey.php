<?php

namespace App\Enums;

enum EmailTemplateKey: string
{
    case CustomerQuoteAcknowledgement = 'customer_quote_acknowledgement';
    case InternalQuoteRequest = 'internal_quote_request';
    case CustomerFinalQuote = 'customer_final_quote';
    case CustomerQuoteFollowUp = 'customer_quote_follow_up';
    case CustomerReviewRequest = 'customer_review_request';
    case CustomerInvoice = 'customer_invoice';

    public function label(): string
    {
        return match ($this) {
            self::CustomerQuoteAcknowledgement => 'Customer quote acknowledgement',
            self::InternalQuoteRequest => 'Internal new lead alert',
            self::CustomerFinalQuote => 'Customer final quote',
            self::CustomerQuoteFollowUp => 'Customer 24-hour follow-up',
            self::CustomerReviewRequest => 'Customer review request',
            self::CustomerInvoice => 'Customer invoice',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::CustomerQuoteAcknowledgement => 'Sent to the customer when a new estimate request is submitted or resent from CRM.',
            self::InternalQuoteRequest => 'Sent to lead notification emails when a new estimate request arrives.',
            self::CustomerFinalQuote => 'Sent when a final quoted amount is saved or sent from CRM.',
            self::CustomerQuoteFollowUp => 'Queued follow-up for leads still in “New” after about 24 hours.',
            self::CustomerReviewRequest => 'Queued after a booking is marked completed, asking for a Google review.',
            self::CustomerInvoice => 'Sent when an issued invoice PDF is emailed to the customer from CRM.',
        };
    }

    /**
     * @return list<string>
     */
    public function placeholders(): array
    {
        return match ($this) {
            self::CustomerQuoteAcknowledgement => [
                'first_name', 'last_name', 'full_name', 'reference', 'service_name',
                'preferred_date', 'arrival_window', 'guide_estimate', 'business_name',
                'business_phone', 'email',
            ],
            self::InternalQuoteRequest => [
                'full_name', 'reference', 'source', 'phone', 'email', 'postcode',
                'service_name', 'guide_estimate', 'business_name',
            ],
            self::CustomerFinalQuote => [
                'first_name', 'last_name', 'full_name', 'reference', 'service_name',
                'preferred_date', 'arrival_window', 'final_quote', 'guide_estimate',
                'business_name', 'business_phone', 'email',
            ],
            self::CustomerQuoteFollowUp => [
                'first_name', 'full_name', 'reference', 'business_name', 'business_phone', 'whatsapp_url',
            ],
            self::CustomerReviewRequest => [
                'first_name', 'full_name', 'booking_reference', 'business_name',
                'google_review_url', 'business_phone',
            ],
            self::CustomerInvoice => [
                'first_name', 'full_name', 'invoice_number', 'booking_reference',
                'service_name', 'total', 'amount_due', 'due_date', 'issue_date',
                'business_name', 'business_phone', 'email',
            ],
        };
    }
}
