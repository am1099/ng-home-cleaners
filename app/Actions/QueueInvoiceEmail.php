<?php

namespace App\Actions;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Jobs\SendInvoiceMailJob;
use App\Models\Invoice;
use App\Models\InvoiceDelivery;
use App\Models\User;
use InvalidArgumentException;

final class QueueInvoiceEmail
{
    public function handle(
        Invoice $invoice,
        ?string $recipientEmail = null,
        ?User $sentBy = null,
        ?string $subjectOverride = null,
        ?string $bodyOverride = null,
    ): InvoiceDelivery {
        if ($invoice->isDraft() || $invoice->isVoid()) {
            throw new InvalidArgumentException('Only issued invoices can be emailed.');
        }

        if (! in_array($invoice->status, [
            InvoiceStatus::Issued,
            InvoiceStatus::Sent,
            InvoiceStatus::Paid,
        ], true)) {
            throw new InvalidArgumentException('This invoice cannot be emailed in its current status.');
        }

        $email = $recipientEmail ?: $invoice->customer_email;

        if (! filled($email)) {
            throw new InvalidArgumentException(
                'No customer email is available. Enter a recipient email to send this invoice.',
            );
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Enter a valid recipient email address.');
        }

        if (blank($invoice->pdf_path)) {
            throw new InvalidArgumentException('The invoice PDF must exist before it can be emailed.');
        }

        $delivery = InvoiceDelivery::query()->create([
            'invoice_id' => $invoice->id,
            'recipient_email' => $email,
            'status' => InvoiceDeliveryStatus::Queued,
            'sent_by' => $sentBy?->id,
        ]);

        SendInvoiceMailJob::dispatch(
            $delivery->id,
            filled($subjectOverride) ? trim($subjectOverride) : null,
            filled($bodyOverride) ? trim($bodyOverride) : null,
        );

        return $delivery;
    }
}
