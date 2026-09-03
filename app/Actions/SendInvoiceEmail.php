<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\User;

/**
 * Convenience alias used by CRM actions: queue an invoice email delivery.
 */
final class SendInvoiceEmail
{
    public function __construct(
        private readonly QueueInvoiceEmail $queueInvoiceEmail,
    ) {}

    public function handle(
        Invoice $invoice,
        ?string $recipientEmail = null,
        ?User $sentBy = null,
        ?string $subjectOverride = null,
        ?string $bodyOverride = null,
    ): InvoiceDelivery {
        return $this->queueInvoiceEmail->handle(
            $invoice,
            $recipientEmail,
            $sentBy,
            $subjectOverride,
            $bodyOverride,
        );
    }
}
