<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Builds a Google Ads click-conversion upload for confirmed bookings.
 *
 * One identifier per row (gclid, otherwise gbraid, otherwise wbraid).
 * The Order ID is the booking reference so a repeated upload is ignored.
 * Value is the agreed booking price, never the guide estimate.
 */
final class GoogleAdsOfflineConversionExport
{
    public function __construct(private readonly AdAttribution $attribution) {}

    /**
     * @return Collection<int, Booking>
     */
    public function pending(): Collection
    {
        return Booking::query()
            ->with('quoteRequest')
            ->whereIn('status', [BookingStatus::Scheduled->value, BookingStatus::Completed->value])
            ->whereNull('ads_offline_exported_at')
            ->where('agreed_price_pence', '>', 0)
            ->whereHas('quoteRequest', fn ($query) => $query->whereNotNull('ad_attribution'))
            ->orderBy('id')
            ->get()
            ->filter(fn (Booking $booking): bool => $this->row($booking) !== null)
            ->values();
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     */
    public function toCsv(Collection $bookings): string
    {
        $handle = fopen('php://temp', 'r+');

        if ($handle === false) {
            throw new \RuntimeException('Could not build the Google Ads export.');
        }

        fputcsv($handle, ['Parameters:TimeZone=Europe/London']);
        fputcsv($handle, [
            'Google Click ID',
            'GBRAID',
            'WBRAID',
            'Conversion Name',
            'Conversion Time',
            'Conversion Value',
            'Conversion Currency',
            'Order ID',
            'Ad User Data',
            'Ad Personalization',
        ]);

        foreach ($bookings as $booking) {
            $row = $this->row($booking);

            if ($row === null) {
                continue;
            }

            fputcsv($handle, $row);
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv === false ? '' : $csv;
    }

    /**
     * @param  Collection<int, Booking>  $bookings
     */
    public function markExported(Collection $bookings): void
    {
        if ($bookings->isEmpty()) {
            return;
        }

        Booking::query()
            ->whereIn('id', $bookings->pluck('id')->all())
            ->whereNull('ads_offline_exported_at')
            ->update(['ads_offline_exported_at' => now()]);
    }

    /**
     * @return list<string>|null
     */
    public function row(Booking $booking): ?array
    {
        $quote = $booking->quoteRequest;
        $click = $this->attribution->exportClick($quote?->ad_attribution);

        if ($quote === null || $click === null || (int) $booking->agreed_price_pence <= 0) {
            return null;
        }

        $clickedAt = Carbon::parse($click['clicked_at']);
        $convertedAt = $booking->created_at ?? now();

        if ($clickedAt->lt(now()->subDays(AdAttribution::MAX_AGE_DAYS))) {
            return null;
        }

        if ($convertedAt->lt($clickedAt)) {
            $convertedAt = $clickedAt->copy()->addMinute();
        }

        if ($clickedAt->diffInDays($convertedAt) > AdAttribution::MAX_AGE_DAYS) {
            return null;
        }

        $ids = ['gclid' => '', 'gbraid' => '', 'wbraid' => ''];
        $ids[$click['field']] = $click['value'];

        $name = (string) config('analytics.google_ads_booked_conversion_name');

        if ($name === '') {
            return null;
        }

        return [
            $ids['gclid'],
            $ids['gbraid'],
            $ids['wbraid'],
            $name,
            $convertedAt->timezone('Europe/London')->format('Y-m-d H:i:s'),
            number_format(((int) $booking->agreed_price_pence) / 100, 2, '.', ''),
            'GBP',
            (string) $booking->reference,
            'Granted',
            'Granted',
        ];
    }
}
