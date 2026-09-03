<?php

namespace App\Jobs;

use App\Enums\InvoiceDeliveryStatus;
use App\Enums\InvoiceStatus;
use App\Mail\CustomerInvoiceMail;
use App\Models\InvoiceDelivery;
use App\Services\InvoicePdfService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendInvoiceMailJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(
        public int $deliveryId,
        public ?string $subjectOverride = null,
        public ?string $bodyOverride = null,
    ) {}

    public function handle(InvoicePdfService $invoicePdfService): void
    {
        $delivery = InvoiceDelivery::query()
            ->with(['invoice.items', 'invoice.booking', 'invoice.customer'])
            ->find($this->deliveryId);

        if (! $delivery) {
            return;
        }

        if ($delivery->status === InvoiceDeliveryStatus::Sent) {
            return;
        }

        $invoice = $delivery->invoice;

        if (! $invoice || $invoice->isVoid() || $invoice->isDraft()) {
            $delivery->forceFill([
                'status' => InvoiceDeliveryStatus::Failed,
                'failed_at' => now(),
                'error_summary' => 'Invoice is not available to send.',
            ])->save();

            return;
        }

        try {
            $pdfContents = $invoicePdfService->storedPdfContents($invoice);

            Mail::to($delivery->recipient_email)->send(
                new CustomerInvoiceMail(
                    $invoice,
                    $pdfContents,
                    $this->subjectOverride,
                    $this->bodyOverride,
                ),
            );

            $delivery->forceFill([
                'status' => InvoiceDeliveryStatus::Sent,
                'sent_at' => now(),
                'failed_at' => null,
                'error_summary' => null,
            ])->save();

            $invoice->forceFill([
                'first_sent_at' => $invoice->first_sent_at ?? now(),
                'last_sent_at' => now(),
                'status' => $invoice->status === InvoiceStatus::Issued
                    ? InvoiceStatus::Sent
                    : $invoice->status,
            ])->save();
        } catch (Throwable $exception) {
            Log::warning('Invoice email delivery failed.', [
                'delivery_id' => $delivery->id,
                'invoice_id' => $invoice->id,
                'message' => $exception->getMessage(),
            ]);

            $delivery->forceFill([
                'status' => InvoiceDeliveryStatus::Failed,
                'failed_at' => now(),
                'error_summary' => mb_substr($exception->getMessage(), 0, 240),
            ])->save();

            throw $exception;
        }
    }
}
