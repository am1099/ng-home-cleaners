<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Pricing\Money;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class DashboardMetrics
{
    public function __construct(
        private readonly RevenueCalculator $revenue,
    ) {}

    /**
     * @return array{
     *     new_leads: int,
     *     leads_this_month: int,
     *     leads_last_month: int,
     *     awaiting_response: int,
     *     upcoming_bookings: int,
     *     completed_jobs_this_month: int,
     *     completed_jobs_last_month: int,
     *     revenue_this_month_pence: int,
     *     revenue_last_month_pence: int,
     *     revenue_all_time_pence: int,
     *     outstanding_balance_pence: int,
     *     conversion_rate: float|null,
     *     conversion_rate_last_month: float|null,
     *     won_this_month: int,
     *     decided_this_month: int,
     *     most_requested_service: ?array{id: int, name: string, count: int}
     * }
     */
    public function snapshot(?Carbon $now = null): array
    {
        $now = ($now ?? now())->copy();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $lastMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth();
        $today = $now->toDateString();

        $leadStatusCounts = QuoteRequest::query()
            ->select('status', DB::raw('count(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $newLeads = (int) ($leadStatusCounts[QuoteRequestStatus::New->value] ?? 0);
        $awaitingResponse = (int) ($leadStatusCounts[QuoteRequestStatus::QuoteSent->value] ?? 0);

        $leadsThisMonth = QuoteRequest::query()
            ->whereBetween('submitted_at', [$monthStart, $monthEnd])
            ->count();

        $leadsLastMonth = QuoteRequest::query()
            ->whereBetween('submitted_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $upcomingBookings = Booking::query()
            ->where('status', BookingStatus::Scheduled)
            ->whereDate('booking_date', '>=', $today)
            ->count();

        $completedThisMonth = Booking::query()
            ->where('status', BookingStatus::Completed)
            ->where(function ($query) use ($monthStart, $monthEnd): void {
                $query->whereBetween('completed_at', [$monthStart, $monthEnd])
                    ->orWhere(function ($inner) use ($monthStart, $monthEnd): void {
                        $inner->whereNull('completed_at')
                            ->whereBetween('updated_at', [$monthStart, $monthEnd]);
                    });
            })
            ->count();

        $completedLastMonth = Booking::query()
            ->where('status', BookingStatus::Completed)
            ->where(function ($query) use ($lastMonthStart, $lastMonthEnd): void {
                $query->whereBetween('completed_at', [$lastMonthStart, $lastMonthEnd])
                    ->orWhere(function ($inner) use ($lastMonthStart, $lastMonthEnd): void {
                        $inner->whereNull('completed_at')
                            ->whereBetween('updated_at', [$lastMonthStart, $lastMonthEnd]);
                    });
            })
            ->count();

        $wonThisMonth = QuoteRequest::query()
            ->where('status', QuoteRequestStatus::Won)
            ->whereBetween('won_at', [$monthStart, $monthEnd])
            ->count();

        $lostThisMonth = QuoteRequest::query()
            ->where('status', QuoteRequestStatus::Lost)
            ->whereBetween('lost_at', [$monthStart, $monthEnd])
            ->count();

        $wonLastMonth = QuoteRequest::query()
            ->where('status', QuoteRequestStatus::Won)
            ->whereBetween('won_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $lostLastMonth = QuoteRequest::query()
            ->where('status', QuoteRequestStatus::Lost)
            ->whereBetween('lost_at', [$lastMonthStart, $lastMonthEnd])
            ->count();

        $decidedThisMonth = $wonThisMonth + $lostThisMonth;
        $decidedLastMonth = $wonLastMonth + $lostLastMonth;

        $mostRequested = QuoteRequest::query()
            ->select('service_id', DB::raw('count(*) as aggregate'))
            ->whereNotNull('service_id')
            ->groupBy('service_id')
            ->orderByDesc('aggregate')
            ->first();

        $mostRequestedService = null;
        if ($mostRequested) {
            $service = Service::query()->find($mostRequested->service_id);
            if ($service) {
                $mostRequestedService = [
                    'id' => (int) $service->id,
                    'name' => $service->name,
                    'count' => (int) $mostRequested->aggregate,
                ];
            }
        }

        return [
            'new_leads' => $newLeads,
            'leads_this_month' => $leadsThisMonth,
            'leads_last_month' => $leadsLastMonth,
            'awaiting_response' => $awaitingResponse,
            'upcoming_bookings' => $upcomingBookings,
            'completed_jobs_this_month' => $completedThisMonth,
            'completed_jobs_last_month' => $completedLastMonth,
            'revenue_this_month_pence' => $this->revenue->totalPence($monthStart, $monthEnd),
            'revenue_last_month_pence' => $this->revenue->totalPence($lastMonthStart, $lastMonthEnd),
            'revenue_all_time_pence' => $this->revenue->totalPence(),
            'outstanding_balance_pence' => $this->outstandingBalancePence(),
            'conversion_rate' => $this->conversionRate($wonThisMonth, $decidedThisMonth),
            'conversion_rate_last_month' => $this->conversionRate($wonLastMonth, $decidedLastMonth),
            'won_this_month' => $wonThisMonth,
            'decided_this_month' => $decidedThisMonth,
            'most_requested_service' => $mostRequestedService,
        ];
    }

    public function outstandingBalancePence(): int
    {
        $paidSubquery = Payment::query()
            ->selectRaw('booking_id, coalesce(sum(amount_pence), 0) as paid_pence')
            ->groupBy('booking_id');

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $outstandingExpression = 'max(0, bookings.agreed_price_pence - coalesce(paid.paid_pence, 0))';
        } else {
            $outstandingExpression = 'greatest(0, bookings.agreed_price_pence - coalesce(paid.paid_pence, 0))';
        }

        return (int) Booking::query()
            ->where('bookings.status', '!=', BookingStatus::Cancelled->value)
            ->leftJoinSub($paidSubquery, 'paid', 'paid.booking_id', '=', 'bookings.id')
            ->selectRaw("coalesce(sum({$outstandingExpression}), 0) as outstanding")
            ->value('outstanding');
    }

    public function conversionRate(int $won, int $decided): ?float
    {
        if ($decided <= 0) {
            return null;
        }

        return round(($won / $decided) * 100, 1);
    }

    public function formatMoney(int $pence): string
    {
        return Money::formatPence($pence);
    }

    public function percentChangeDescription(int|float|null $current, int|float|null $previous, string $unit = ''): ?string
    {
        if ($previous === null || $current === null) {
            return null;
        }

        if ((float) $previous === 0.0) {
            if ((float) $current === 0.0) {
                return 'Same as last month';
            }

            return 'Up from zero last month';
        }

        $change = (((float) $current - (float) $previous) / (float) $previous) * 100;
        $rounded = round(abs($change));

        if ($rounded === 0.0) {
            return 'Same as last month';
        }

        $direction = $change > 0 ? 'Up' : 'Down';
        $suffix = $unit !== '' ? " {$unit}" : '';

        return "{$direction} {$rounded}%{$suffix} vs last month";
    }

    public function countChangeDescription(int $current, int $previous): string
    {
        if ($previous === 0 && $current === 0) {
            return 'Same as last month';
        }

        if ($previous === 0) {
            return 'Up from zero last month';
        }

        $diff = $current - $previous;

        if ($diff === 0) {
            return 'Same as last month';
        }

        $direction = $diff > 0 ? 'Up' : 'Down';

        return "{$direction} ".abs($diff).' vs last month';
    }

    /**
     * @return Collection<int, array{type: string, title: string, subtitle: string, at: Carbon, url: string|null}>
     */
    public function recentActivity(int $limit = 8): Collection
    {
        $leads = QuoteRequest::query()
            ->with('service:id,name')
            ->latest('submitted_at')
            ->limit($limit)
            ->get(['id', 'reference', 'first_name', 'last_name', 'status', 'service_id', 'submitted_at'])
            ->map(fn (QuoteRequest $lead): array => [
                'type' => 'lead',
                'title' => 'Lead '.$lead->reference,
                'subtitle' => $lead->fullName().' · '.($lead->service?->name ?? 'Service').' · '.$lead->status->label(),
                'at' => $lead->submitted_at ?? $lead->created_at,
                'url' => QuoteRequestResource::getUrl('view', ['record' => $lead]),
            ]);

        $bookings = Booking::query()
            ->with(['customer:id,first_name,last_name', 'service:id,name'])
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'reference', 'customer_id', 'service_id', 'status', 'booking_date', 'created_at'])
            ->map(fn (Booking $booking): array => [
                'type' => 'booking',
                'title' => 'Booking '.$booking->reference,
                'subtitle' => ($booking->customer?->fullName() ?? 'Customer').' · '.($booking->service?->name ?? 'Service').' · '.$booking->status->label(),
                'at' => $booking->created_at,
                'url' => BookingResource::getUrl('view', ['record' => $booking]),
            ]);

        $payments = Payment::query()
            ->with(['booking:id,reference'])
            ->latest('paid_date')
            ->latest('id')
            ->limit($limit)
            ->get(['id', 'booking_id', 'amount_pence', 'type', 'paid_date', 'created_at'])
            ->map(fn (Payment $payment): array => [
                'type' => 'payment',
                'title' => $payment->type->label().' payment '.$payment->amountDisplay(),
                'subtitle' => 'Booking '.($payment->booking?->reference ?? '—'),
                'at' => Carbon::parse($payment->paid_date->format('Y-m-d').' '.($payment->created_at?->format('H:i:s') ?? '12:00:00')),
                'url' => $payment->booking_id
                    ? BookingResource::getUrl('view', ['record' => $payment->booking_id])
                    : null,
            ]);

        return $leads
            ->concat($bookings)
            ->concat($payments)
            ->sortByDesc(fn (array $item) => $item['at']?->timestamp ?? 0)
            ->values()
            ->take($limit);
    }
}
