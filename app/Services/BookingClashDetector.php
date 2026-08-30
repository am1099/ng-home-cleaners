<?php

namespace App\Services;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class BookingClashDetector
{
    /**
     * @return Collection<int, Booking>
     */
    public function conflictingBookings(
        Carbon|string $bookingDate,
        ArrivalWindow|string $arrivalWindow,
        ?int $ignoreBookingId = null,
    ): Collection {
        $date = $bookingDate instanceof Carbon
            ? $bookingDate->toDateString()
            : Carbon::parse($bookingDate)->toDateString();

        $window = $arrivalWindow instanceof ArrivalWindow
            ? $arrivalWindow
            : ArrivalWindow::from($arrivalWindow);

        return Booking::query()
            ->with(['customer', 'service'])
            ->whereDate('booking_date', $date)
            ->where('status', '!=', BookingStatus::Cancelled->value)
            ->when($ignoreBookingId, fn ($query) => $query->whereKeyNot($ignoreBookingId))
            ->orderBy('arrival_window')
            ->get()
            ->filter(fn (Booking $booking): bool => $window->conflictsWith($booking->arrival_window));
    }

    public function warningMessage(
        Carbon|string $bookingDate,
        ArrivalWindow|string $arrivalWindow,
        ?int $ignoreBookingId = null,
    ): ?string {
        $clashes = $this->conflictingBookings($bookingDate, $arrivalWindow, $ignoreBookingId);

        if ($clashes->isEmpty()) {
            return null;
        }

        $lines = $clashes->map(function (Booking $booking): string {
            return sprintf(
                '%s — %s (%s, %s)',
                $booking->reference,
                $booking->customer?->fullName() ?? 'Customer',
                $booking->service?->name ?? 'Service',
                $booking->arrival_window->shortLabel(),
            );
        })->implode('; ');

        return 'Possible scheduling clash with '.$clashes->count().' other booking(s) on this day: '.$lines.'. You can still save this booking.';
    }
}
