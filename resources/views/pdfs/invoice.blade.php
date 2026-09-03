<!DOCTYPE html>
<html lang="en-GB">
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->displayNumber() }}</title>
    <style>
        @page { margin: 18mm 14mm 18mm 14mm; }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
            line-height: 1.45;
            color: #1f2937;
            margin: 0;
            padding: 0;
        }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: #6b7280; }
        .accent { color: #0d9488; }
        .right { text-align: right; }
        .left { text-align: left; }
        .bold { font-weight: bold; }
        .header-table td { vertical-align: top; }
        .brand-name {
            font-size: 18px;
            font-weight: bold;
            color: #0f766e;
            margin: 0 0 4px 0;
        }
        .doc-title {
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 0.08em;
            color: #0d9488;
            margin: 0 0 8px 0;
        }
        .meta-label { color: #6b7280; }
        .section {
            margin-top: 22px;
        }
        .section-title {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: #0d9488;
            border-bottom: 1px solid #99f6e4;
            padding-bottom: 4px;
            margin-bottom: 8px;
        }
        .party-table td {
            width: 50%;
            vertical-align: top;
            padding-right: 12px;
        }
        .items th {
            background: #f0fdfa;
            color: #115e59;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            padding: 8px 6px;
            border-bottom: 1px solid #99f6e4;
            text-align: left;
        }
        .items td {
            padding: 8px 6px;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: top;
        }
        .items .qty,
        .items .unit,
        .items .total {
            text-align: right;
            white-space: nowrap;
        }
        .totals {
            width: 280px;
            margin-left: auto;
            margin-top: 12px;
        }
        .totals td {
            padding: 4px 0;
        }
        .totals .grand td {
            padding-top: 8px;
            border-top: 2px solid #0d9488;
            font-size: 13px;
            font-weight: bold;
        }
        .totals .due td {
            padding-top: 6px;
            font-size: 12px;
            font-weight: bold;
            color: #0f766e;
        }
        .footer {
            margin-top: 28px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            color: #6b7280;
        }
        .watermark {
            position: fixed;
            top: 38%;
            left: 12%;
            font-size: 72px;
            font-weight: bold;
            color: #0d9488;
            opacity: 0.08;
            transform: rotate(-28deg);
            letter-spacing: 0.12em;
            z-index: 0;
        }
        .content { position: relative; z-index: 1; }
        .badge-draft {
            display: inline-block;
            margin-top: 6px;
            padding: 3px 8px;
            border: 1px solid #0d9488;
            color: #0d9488;
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.08em;
        }
        .notes {
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
@if ($isDraft)
    <div class="watermark">DRAFT</div>
@endif

<div class="content">
    <table class="header-table">
        <tr>
            <td class="left" style="width: 58%;">
                @if (! empty($logoDataUri))
                    <img src="{{ $logoDataUri }}" alt="{{ $invoice->business_name }}" style="max-height: 48px; max-width: 180px; margin-bottom: 8px;">
                @endif
                <p class="brand-name">{{ $invoice->business_name }}</p>
                @if (filled($invoice->company_legal_name) && $invoice->company_legal_name !== $invoice->business_name)
                    <div class="muted">{{ $invoice->company_legal_name }}</div>
                @endif
                @if (filled($invoice->business_address))
                    <div class="muted">{{ $invoice->business_address }}</div>
                @endif
                @if (filled($invoice->business_phone))
                    <div class="muted">{{ $invoice->business_phone }}</div>
                @endif
                @if (filled($invoice->business_email))
                    <div class="muted">{{ $invoice->business_email }}</div>
                @endif
                @if ($invoice->vat_registered && filled($invoice->vat_number))
                    <div class="muted">VAT number: {{ $invoice->vat_number }}</div>
                @endif
            </td>
            <td class="right" style="width: 42%;">
                <p class="doc-title">{{ $invoice->vat_registered ? 'VAT INVOICE' : 'INVOICE' }}</p>
                @if ($isDraft)
                    <div class="badge-draft">DRAFT</div>
                @endif
                <table style="margin-top: 10px;">
                    <tr>
                        <td class="meta-label right">Invoice</td>
                        <td class="right bold" style="padding-left: 12px;">{{ $invoice->displayNumber() }}</td>
                    </tr>
                    <tr>
                        <td class="meta-label right">Issue date</td>
                        <td class="right" style="padding-left: 12px;">
                            {{ $invoice->issue_date?->format('j F Y') ?: ($isDraft ? 'Not issued' : '—') }}
                        </td>
                    </tr>
                    <tr>
                        <td class="meta-label right">Due date</td>
                        <td class="right" style="padding-left: 12px;">
                            {{ $invoice->due_date?->format('j F Y') ?: '—' }}
                        </td>
                    </tr>
                    @if (filled($invoice->booking_reference))
                        <tr>
                            <td class="meta-label right">Booking</td>
                            <td class="right" style="padding-left: 12px;">{{ $invoice->booking_reference }}</td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    </table>

    <div class="section">
        <table class="party-table">
            <tr>
                <td>
                    <div class="section-title">From</div>
                    <div class="bold">{{ $invoice->business_name }}</div>
                    @if (filled($invoice->business_address))
                        <div>{{ $invoice->business_address }}</div>
                    @endif
                    @if (filled($invoice->business_email))
                        <div>{{ $invoice->business_email }}</div>
                    @endif
                    @if (filled($invoice->business_phone))
                        <div>{{ $invoice->business_phone }}</div>
                    @endif
                </td>
                <td>
                    <div class="section-title">Bill to</div>
                    <div class="bold">{{ $invoice->customer_name }}</div>
                    @if (filled($invoice->billingAddressDisplay()))
                        <div>{{ $invoice->billingAddressDisplay() }}</div>
                    @endif
                    @if (filled($invoice->customer_email))
                        <div>{{ $invoice->customer_email }}</div>
                    @endif
                    @if (filled($invoice->customer_phone))
                        <div>{{ $invoice->customer_phone }}</div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    @if (filled($invoice->service_name) || filled($invoice->booking_date))
        <div class="section">
            <div class="section-title">Booking / service</div>
            <table>
                @if (filled($invoice->service_name))
                    <tr>
                        <td class="muted" style="width: 120px;">Service</td>
                        <td>{{ $invoice->service_name }}</td>
                    </tr>
                @endif
                @if (filled($invoice->booking_reference))
                    <tr>
                        <td class="muted">Booking reference</td>
                        <td>{{ $invoice->booking_reference }}</td>
                    </tr>
                @endif
                @if ($invoice->booking_date)
                    <tr>
                        <td class="muted">Service date</td>
                        <td>{{ $invoice->booking_date->format('j F Y') }}</td>
                    </tr>
                @endif
            </table>
        </div>
    @endif

    <div class="section">
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 48%;">Description</th>
                    <th class="right" style="width: 12%;">Qty</th>
                    <th class="right" style="width: 20%;">Unit price</th>
                    <th class="right" style="width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($invoice->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="qty">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }}</td>
                        <td class="unit">{{ $item->unitPriceDisplay() }}</td>
                        <td class="total">{{ $item->lineTotalDisplay() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <table class="totals">
            <tr>
                <td class="muted">Subtotal</td>
                <td class="right">{{ $invoice->subtotalDisplay() }}</td>
            </tr>
            @if ((int) $invoice->discount_pence > 0)
                <tr>
                    <td class="muted">Discount</td>
                    <td class="right">-{{ $invoice->discountDisplay() }}</td>
                </tr>
            @endif
            @if ($invoice->vat_registered)
                <tr>
                    <td class="muted">
                        VAT
                        @if (filled($invoice->vat_rate_percent))
                            ({{ rtrim(rtrim(number_format((float) $invoice->vat_rate_percent, 2, '.', ''), '0'), '.') }}%)
                        @endif
                    </td>
                    <td class="right">{{ $invoice->vatDisplay() }}</td>
                </tr>
            @endif
            <tr class="grand">
                <td>Total</td>
                <td class="right">{{ $invoice->totalDisplay() }}</td>
            </tr>
            <tr>
                <td class="muted">Already paid</td>
                <td class="right">{{ $invoice->paidDisplay() }}</td>
            </tr>
            <tr class="due">
                <td>Amount due</td>
                <td class="right">{{ $invoice->amountDueDisplay() }}</td>
            </tr>
        </table>
    </div>

    @if (filled($invoice->payment_terms) || filled($invoice->payment_instructions) || filled($invoice->notes))
        <div class="section">
            @if (filled($invoice->payment_terms))
                <div class="section-title">Payment terms</div>
                <div class="notes">{{ $invoice->payment_terms }}</div>
            @endif
            @if (filled($invoice->payment_instructions))
                <div class="section-title" style="margin-top: 14px;">Payment instructions</div>
                <div class="notes">{{ $invoice->payment_instructions }}</div>
            @endif
            @if (filled($invoice->notes))
                <div class="section-title" style="margin-top: 14px;">Notes</div>
                <div class="notes">{{ $invoice->notes }}</div>
            @endif
        </div>
    @endif

    <div class="footer">
        Thank you for choosing {{ $invoice->business_name }}.
        @if (filled($invoice->company_registration_number))
            <br>Company registration number: {{ $invoice->company_registration_number }}
        @endif
        @if ($invoice->vat_registered && filled($invoice->vat_number))
            <br>VAT number: {{ $invoice->vat_number }}
        @endif
    </div>
</div>
</body>
</html>
