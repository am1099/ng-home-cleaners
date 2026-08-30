<?php

namespace App\Services;

use App\Models\Payment;
use App\Pricing\Money;
use Illuminate\Support\Carbon;

final class RevenueCalculator
{
    /**
     * Revenue is money actually received (net of refunds / outbound adjustments).
     * Quote estimates and unpaid booking totals are never counted.
     */
    public function totalPence(?Carbon $from = null, ?Carbon $to = null): int
    {
        $query = Payment::query();

        if ($from !== null) {
            $query->whereDate('paid_date', '>=', $from->toDateString());
        }

        if ($to !== null) {
            $query->whereDate('paid_date', '<=', $to->toDateString());
        }

        return (int) $query->sum('amount_pence');
    }

    public function totalFormatted(?Carbon $from = null, ?Carbon $to = null): string
    {
        return Money::formatPence($this->totalPence($from, $to));
    }
}
