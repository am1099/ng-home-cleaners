<?php

namespace App\Filament\Resources\Invoices\Widgets;

use App\Enums\InvoiceStatus;
use App\Models\Invoice;
use App\Pricing\Money;
use Filament\Support\Icons\Heroicon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class InvoiceStatsOverview extends StatsOverviewWidget
{
    protected static bool $isDiscovered = false;

    protected static bool $isLazy = false;

    protected ?string $heading = null;

    /**
     * @var int | array<string, ?int> | null
     */
    protected int|array|null $columns = 3;

    protected function getStats(): array
    {
        $openInvoices = Invoice::query()
            ->with('booking.payments')
            ->whereIn('status', [
                InvoiceStatus::Issued->value,
                InvoiceStatus::Sent->value,
                InvoiceStatus::Paid->value,
            ])
            ->get();

        $outstandingPence = (int) $openInvoices
            ->reject(fn (Invoice $invoice): bool => $invoice->status === InvoiceStatus::Paid)
            ->sum(fn (Invoice $invoice): int => $invoice->outstandingPence());

        $paidThisMonthPence = (int) Invoice::query()
            ->where('status', InvoiceStatus::Paid->value)
            ->whereNotNull('paid_at')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('total_pence');

        $overdue = $openInvoices->filter(fn (Invoice $invoice): bool => $invoice->isOverdue());
        $overdueCount = $overdue->count();
        $overduePence = (int) $overdue->sum(fn (Invoice $invoice): int => $invoice->outstandingPence());

        return [
            Stat::make('Outstanding', Money::formatPenceExact($outstandingPence))
                ->description('Amount still due on open invoices')
                ->descriptionIcon(Heroicon::OutlinedBanknotes)
                ->color($outstandingPence > 0 ? 'warning' : 'success'),

            Stat::make('Paid this month', Money::formatPenceExact($paidThisMonthPence))
                ->description('Invoices marked paid this month')
                ->descriptionIcon(Heroicon::OutlinedCheckCircle)
                ->color('success'),

            Stat::make('Overdue', (string) $overdueCount)
                ->description($overdueCount === 0
                    ? 'No overdue invoices'
                    : Money::formatPenceExact($overduePence).' overdue')
                ->descriptionIcon(Heroicon::OutlinedExclamationTriangle)
                ->color($overdueCount > 0 ? 'danger' : 'success'),
        ];
    }
}
