<?php

namespace App\Services\Analytics;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\QuoteRequest;
use App\Models\SiteEvent;
use App\Pricing\Money;
use App\Services\SiteEventRecorder;

final class EnquiryAttribution
{
    public const EVENTS_SINCE = '2026-11-25';

    /**
     * @return array<string, mixed>
     */
    public function summarise(ReportingPeriod $period, ?int $spendPence, bool $spendIsPounds): array
    {
        $quotes = QuoteRequest::query()
            ->whereBetween('submitted_at', [$period->start, $period->end])
            ->get()
            ->filter(fn (QuoteRequest $quote): bool => $this->hasClickId($quote));

        $bookings = Booking::query()
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->whereBetween('created_at', [$period->start, $period->end])
            ->whereHas('quoteRequest', function ($query): void {
                $query->whereNotNull('ad_attribution');
            })
            ->with('quoteRequest:id,ad_attribution')
            ->get()
            ->filter(fn (Booking $booking): bool => $booking->quoteRequest && $this->hasClickId($booking->quoteRequest));

        $eventsCoverPeriod = $period->start->toDateString() >= self::EVENTS_SINCE;
        $whatsapp = $this->eventCount(SiteEventRecorder::WHATSAPP_OPENED, $period);
        $phone = $this->eventCount(SiteEventRecorder::PHONE_CLICKED, $period);

        return [
            'quotes' => $quotes->count(),
            'bookings' => $bookings->count(),
            'whatsapp' => $whatsapp,
            'phone' => $phone,
            'events_cover_period' => $eventsCoverPeriod,
            'events_note' => 'Website events have been recorded since 25 November 2026. A saved enquiry without a stored click ID is not treated as organic, and it is not treated as an ad.',
            'cost_per_whatsapp' => $this->cost($spendPence, $spendIsPounds, $eventsCoverPeriod ? $whatsapp : null),
            'cost_per_booking' => $this->cost($spendPence, $spendIsPounds, $bookings->count()),
            'cost_note' => 'Ad spend is for ads shown in this period. Booked jobs are CRM bookings created in this period that still have a Google click ID. This is not profit.',
        ];
    }

    private function eventCount(string $event, ReportingPeriod $period): int
    {
        return SiteEvent::query()
            ->where('event', $event)
            ->where('from_ads', true)
            ->whereBetween('occurred_at', [$period->start, $period->end])
            ->count();
    }

    private function hasClickId(QuoteRequest $quote): bool
    {
        $latest = is_array($quote->ad_attribution) ? ($quote->ad_attribution['latest'] ?? null) : null;

        if (! is_array($latest)) {
            return false;
        }

        return filled($latest['gclid'] ?? null)
            || filled($latest['gbraid'] ?? null)
            || filled($latest['wbraid'] ?? null);
    }

    private function cost(?int $spendPence, bool $spendIsPounds, ?int $denominator): ?string
    {
        if (! $spendIsPounds || $spendPence === null || $denominator === null || $denominator < 1) {
            return null;
        }

        return Money::formatPenceExact((int) round($spendPence / $denominator));
    }
}
