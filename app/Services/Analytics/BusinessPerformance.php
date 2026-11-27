<?php

namespace App\Services\Analytics;

use App\Enums\BookingStatus;
use App\Enums\QuoteRequestSource;
use App\Enums\QuoteRequestStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\Payments\PaymentResource;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Models\Booking;
use App\Models\QuoteRequest;
use App\Pricing\Money;
use App\Services\RevenueCalculator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class BusinessPerformance
{
    public function __construct(private readonly RevenueCalculator $revenue) {}

    /**
     * @return array<string, mixed>
     */
    public function summarise(ReportingPeriod $period): array
    {
        $booked = $this->bookedCount($period->start, $period->end);
        $bookedPrevious = $this->bookedCount($period->previousStart, $period->previousEnd);
        $received = $this->revenue->totalPence($period->start, $period->end);
        $receivedPrevious = $this->revenue->totalPence($period->previousStart, $period->previousEnd);
        $quotes = $this->quotes($period->start, $period->end);
        $quotesPrevious = $this->quotes($period->previousStart, $period->previousEnd);
        $attention = $this->attention($period);

        return [
            'range' => $period->label(),
            'previous_range' => $period->previousLabel(),
            'booked' => [
                'count' => $booked,
                'change' => $period->countChange($booked, $bookedPrevious, 'booking', 'bookings'),
                'href' => BookingResource::getUrl('index'),
                'hint' => 'Scheduled or completed. Cancelled jobs are left out.',
            ],
            'received' => [
                'amount' => Money::formatPenceExact($received),
                'pence' => $received,
                'change' => $period->moneyChange($received, $receivedPrevious),
                'href' => PaymentResource::getUrl('index'),
                'hint' => 'Money paid in, after refunds. Unpaid invoices are not included.',
            ],
            'quotes' => [
                'count' => $quotes->count(),
                'change' => $period->countChange($quotes->count(), $quotesPrevious->count(), 'enquiry', 'enquiries'),
                'href' => QuoteRequestResource::getUrl('index'),
                'hint' => 'Saved quote requests, including WhatsApp enquiries.',
            ],
            'attention' => $attention,
        ];
    }

    private function bookedCount(Carbon $start, Carbon $end): int
    {
        return Booking::query()
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    /**
     * @return Collection<int, QuoteRequest>
     */
    private function quotes(Carbon $start, Carbon $end)
    {
        return QuoteRequest::query()
            ->whereBetween('submitted_at', [$start, $end])
            ->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function attention(ReportingPeriod $period): array
    {
        $open = [QuoteRequestStatus::New->value, QuoteRequestStatus::Contacted->value, QuoteRequestStatus::QuoteSent->value];

        $openInPeriod = QuoteRequest::query()
            ->whereIn('status', $open)
            ->whereBetween('submitted_at', [$period->start, $period->end]);

        $inPeriod = (clone $openInPeriod)
            ->with('service:id,name')
            ->orderByDesc('submitted_at')
            ->limit(4)
            ->get();
        $openCount = (clone $openInPeriod)->count();

        $previous = QuoteRequest::query()
            ->whereIn('status', $open)
            ->whereBetween('submitted_at', [$period->previousStart, $period->previousEnd])
            ->count();

        $older = QuoteRequest::query()
            ->whereIn('status', $open)
            ->where('submitted_at', '<', $period->start)
            ->count();

        return [
            'count' => $openCount,
            'change' => $period->countChange($openCount, $previous, 'open enquiry', 'open enquiries'),
            'href' => QuoteRequestResource::getUrl('index'),
            'older' => $older,
            'hint' => 'New, contacted, or quote sent. Won and lost leads are not included.',
            'items' => $inPeriod->take(6)->map(function (QuoteRequest $quote): array {
                $reachable = filled($quote->phone) || filled($quote->email);
                $whatsappWithoutContact = $quote->source === QuoteRequestSource::Whatsapp && ! $reachable;

                return [
                    'reference' => $quote->reference,
                    'service' => $quote->service?->name ?? 'Cleaning',
                    'status' => $quote->status->label(),
                    'note' => $whatsappWithoutContact
                        ? 'WhatsApp enquiry with no phone or email. It cannot be followed up from here.'
                        : ($reachable ? 'Contact details are on the lead.' : 'No phone or email saved.'),
                    'href' => QuoteRequestResource::getUrl('view', ['record' => $quote]),
                ];
            })->take(4)->all(),
        ];
    }
}
