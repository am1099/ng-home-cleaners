<?php

namespace App\Services\Analytics;

use App\Enums\BookingStatus;
use App\Filament\Resources\Bookings\BookingResource;
use App\Filament\Resources\QuoteRequests\QuoteRequestResource;
use App\Models\Booking;
use App\Models\QuoteRequest;
use App\Models\Service;
use App\Pricing\Money;

final class ServicePerformance
{
    /**
     * @return list<array<string, mixed>>
     */
    public function rows(ReportingPeriod $period): array
    {
        $services = Service::query()->orderBy('sort_order')->get();

        return $services->map(function (Service $service) use ($period): array {
            $quotes = QuoteRequest::query()
                ->where('service_id', $service->id)
                ->whereBetween('submitted_at', [$period->start, $period->end])
                ->pluck('id');

            $bookings = Booking::query()
                ->where('service_id', $service->id)
                ->where('status', '!=', BookingStatus::Cancelled->value)
                ->whereBetween('created_at', [$period->start, $period->end]);

            $bookingCount = (clone $bookings)->count();
            $agreed = (clone $bookings)->whereNotNull('agreed_price_pence')->sum('agreed_price_pence');
            $hasAgreed = (clone $bookings)->whereNotNull('agreed_price_pence')->exists();

            $converted = $quotes->isEmpty()
                ? 0
                : QuoteRequest::query()
                    ->whereIn('id', $quotes)
                    ->whereHas('bookings', fn ($query) => $query->where('status', '!=', BookingStatus::Cancelled->value))
                    ->count();

            return [
                'name' => $service->name,
                'quotes' => $quotes->count(),
                'bookings' => $bookingCount,
                'rate' => $quotes->isEmpty() ? null : (int) round(($converted / $quotes->count()) * 100),
                'agreed' => $hasAgreed ? Money::formatPenceExact((int) $agreed) : null,
                'quotes_href' => QuoteRequestResource::getUrl('index', [
                    'tableFilters' => ['service_id' => ['value' => $service->id]],
                ]),
                'bookings_href' => BookingResource::getUrl('index', [
                    'tableFilters' => ['service_id' => ['value' => $service->id]],
                ]),
            ];
        })->all();
    }
}
