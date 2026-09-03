<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\SiteSetting;
use App\Support\InvoiceStorage;
use App\Support\Media;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\LaravelPdf\Facades\Pdf;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class InvoicePdfService
{
    public function previewDraft(Invoice $invoice): PdfBuilder
    {
        $invoice->loadMissing(['items', 'booking', 'customer']);

        return $this->builder($invoice, draft: true)
            ->name('draft-invoice');
    }

    public function generateAndStoreFinalPdf(Invoice $invoice): Invoice
    {
        if (blank($invoice->invoice_number)) {
            throw new RuntimeException('Cannot store a final PDF without an invoice number.');
        }

        $invoice->loadMissing(['items', 'booking', 'customer']);

        $path = InvoiceStorage::pathForNumber((string) $invoice->invoice_number);
        $disk = InvoiceStorage::diskName();

        $this->builder($invoice, draft: false)
            ->driver('dompdf')
            ->format('a4')
            ->disk($disk, 'private')
            ->save($path);

        $invoice->forceFill([
            'pdf_disk' => $disk,
            'pdf_path' => $path,
        ])->save();

        return $invoice;
    }

    public function storedPdfContents(Invoice $invoice): string
    {
        $disk = $invoice->pdf_disk ?: InvoiceStorage::diskName();
        $path = $invoice->pdf_path;

        if (blank($path) || ! Storage::disk($disk)->exists($path)) {
            Log::error('Issued invoice PDF is missing from private storage.', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'pdf_disk' => $disk,
                'pdf_path' => $path,
            ]);

            throw new RuntimeException(
                'The stored PDF for invoice '.($invoice->invoice_number ?: '#'.$invoice->id).' is missing. It will not be regenerated automatically.',
            );
        }

        $contents = Storage::disk($disk)->get($path);

        if (! is_string($contents) || $contents === '') {
            Log::error('Issued invoice PDF could not be read from private storage.', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'pdf_disk' => $disk,
                'pdf_path' => $path,
            ]);

            throw new RuntimeException(
                'The stored PDF for invoice '.($invoice->invoice_number ?: '#'.$invoice->id).' could not be read.',
            );
        }

        return $contents;
    }

    public function streamDraftPreview(Invoice $invoice): Response
    {
        $filename = 'draft-invoice-'.($invoice->booking_reference ?: $invoice->id).'.pdf';

        return $this->previewDraft($invoice)
            ->name($filename)
            ->inline($filename)
            ->toResponse(request());
    }

    public function streamStoredPdf(Invoice $invoice, bool $inline = false): StreamedResponse
    {
        $contents = $this->storedPdfContents($invoice);
        $filename = ($invoice->invoice_number ?: 'invoice').'.pdf';
        $disposition = $inline ? 'inline' : 'attachment';

        return response()->streamDownload(
            function () use ($contents): void {
                echo $contents;
            },
            $filename,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
            ],
            $disposition,
        );
    }

    private function builder(Invoice $invoice, bool $draft): PdfBuilder
    {
        return Pdf::driver('dompdf')
            ->format('a4')
            ->margins(12, 12, 14, 12)
            ->view('pdfs.invoice', [
                'invoice' => $invoice,
                'isDraft' => $draft,
                'logoDataUri' => $this->logoDataUri(),
            ]);
    }

    private function logoDataUri(): ?string
    {
        $settings = SiteSetting::instance();
        $path = $settings->logo_path;

        if (! filled($path)) {
            return null;
        }

        $disk = Media::diskName();

        if (! Storage::disk($disk)->exists($path)) {
            return null;
        }

        $bytes = Storage::disk($disk)->get($path);

        if (! is_string($bytes) || $bytes === '') {
            return null;
        }

        $mime = Storage::disk($disk)->mimeType($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode($bytes);
    }
}
