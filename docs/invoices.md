# Invoices

Professional CRM invoicing for NG Home Cleaners. Invoices are historical financial documents linked to Bookings and settled through the existing Payments module.

## Invoice workflow

```
Booking
  → Create invoice (Draft)
  → Review / edit line items
  → Preview (DRAFT watermark)
  → Issue invoice (number + permanent private PDF)
  → Download PDF / Send by email (queued)
  → Record payments on the Booking
  → Invoice becomes Paid when net payments cover the total
  → Void if a replacement is needed
```

Primary path: open a Booking → **Create invoice**. Standalone create under CRM → Invoices still requires choosing a Booking.

## Status meanings

| Status | Meaning |
|--------|---------|
| Draft | Editable. No invoice number. No final PDF. |
| Issued | Number allocated. Snapshot and PDF locked. |
| Sent | At least one successful email delivery. |
| Paid | Net booking payments cover the invoice total. |
| Void | Kept for history; excluded from outstanding. |

**Overdue** is derived (not stored): due date in the past, outstanding &gt; 0, and status is not Paid/Void/Draft. Shown as a red badge in the CRM.

Paid has higher priority than Sent: resending a paid invoice does not change status back to Sent.

## Invoice numbering

Format: `NG-{YEAR}-{####}` (e.g. `NG-2026-0001`).

- Sequence resets each calendar year.
- Allocated only when **Issue invoice** succeeds (drafts never consume numbers).
- Concurrency-safe via `invoice_number_counters` + `lockForUpdate()` inside a transaction.
- Unique database constraint on `invoice_number`.
- Voided numbers are never reused.

## Payment integration

Do **not** record payments on the invoice itself.

```
Booking
├── Invoice (snapshot of what was billed)
└── Payments (authoritative cash movements)
```

- Paid = sum of booking `payments.amount_pence` (refunds are negative).
- Outstanding / amount due = invoice total − paid (floored at zero for display where appropriate).
- `InvoiceBalanceService` syncs Paid / unpaid status when payments are created, updated, or deleted.

Revenue on the Dashboard remains based on Payments, not issued invoice totals.

## VAT configuration

Site settings → **Invoices** tab:

- VAT registered? (default **No**)
- VAT number
- Default VAT rate (%)
- Default due days (0 = due on receipt)
- Payment terms / payment instructions / default notes
- Company legal name / registration number

When VAT registered is false: no VAT line, no VAT number on the PDF, document is not labelled a VAT invoice.

VAT rate and amount are snapshotted on the invoice at issue time and are not rewritten if Site Settings change later.

## Storage

Issued PDFs are private:

- Path pattern: `invoices/{YEAR}/{INVOICE_NUMBER}.pdf`
- Disk resolved by `App\Support\InvoiceStorage` (`INVOICE_DISK` override, else local `storage/app/private`, else usable Cloud object storage)
- Never stored under a public web directory
- Download only via authenticated Filament actions (invoice ID → server-side path)

Missing stored PDFs are logged and raise an error — they are **not** silently regenerated (immutability).

## Email delivery

1. Admin confirms Send invoice (To / Subject / Message).
2. `QueueInvoiceEmail` creates a Queued delivery row and dispatches `SendInvoiceMailJob`.
3. Job loads the **saved** PDF bytes and attaches them (same document as Download).
4. Delivery becomes Sent or Failed; invoice `first_sent_at` / `last_sent_at` update on success.
5. CRM copy can be customised under Settings → Email templates (`Customer invoice`).

Failed sends leave the invoice Issued/Sent/Paid intact; admins can retry.

## Important immutability rules

Once issued:

- Invoice number, line items, customer/business snapshots, and totals must not be casually edited.
- Booking, Customer, or Site Settings changes must not rewrite the invoice or its PDF.
- To correct a serious mistake: **Void** the invoice, then create a replacement from the Booking (after void, create is allowed again).

Drafts may be deleted. Issued invoices may not.

## Manual test checklist

### Scenario A — happy path

1. Booking with agreed £225 and £50 deposit.
2. Create invoice → Draft totals £225, already paid £50, amount due £175.
3. Preview → DRAFT shown.
4. Issue → number like `NG-2026-0001`, PDF stored.
5. Download PDF → professional A4 layout.
6. Send invoice → queued message; delivery history Sent after worker runs.
7. Record £175 balance payment → Invoice Paid, outstanding £0.
8. Resend → remains Paid.

### Scenario B — immutability

1. Issue an invoice.
2. Change Booking price, Customer address, and business phone in Site Settings.
3. Reload invoice → original snapshot and PDF unchanged.

### Scenario C — void

1. Void an issued invoice with optional reason.
2. Number, PDF, and history retained; not outstanding; cannot delete.

### Scenario D — draft delete

1. Create draft, delete it.
2. Confirm no invoice number was consumed.
