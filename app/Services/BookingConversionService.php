<?php

namespace App\Services;

use App\Enums\ArrivalWindow;
use App\Enums\BookingStatus;
use App\Enums\QuoteRequestStatus;
use App\Models\Booking;
use App\Models\QuoteRequest;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class BookingConversionService
{
    public function __construct(
        private readonly BookingReferenceGenerator $references,
    ) {}

    /**
     * Prefill attributes for a booking created from a won lead.
     *
     * @return array<string, mixed>
     */
    public function prefillFromLead(QuoteRequest $lead): array
    {
        $address = trim(collect([
            $lead->address_line1,
            $lead->address_line2,
            $lead->city,
        ])->filter()->implode(', '));

        $notes = collect([
            $lead->internal_notes ? 'Lead notes: '.$lead->internal_notes : null,
            $lead->access_notes ? 'Access: '.$lead->access_notes : null,
            $lead->parking_notes ? 'Parking: '.$lead->parking_notes : null,
            'Converted from lead '.$lead->reference.'.',
        ])->filter()->implode("\n\n");

        $agreed = $lead->final_quote_amount_pence
            ?? $lead->guide_single_price_pence
            ?? $lead->guide_estimate_min_pence
            ?? 0;

        return [
            'customer_id' => $lead->customer_id,
            'quote_request_id' => $lead->id,
            'service_id' => $lead->service_id,
            'address_line1' => $lead->address_line1 ?: ($address !== '' ? $address : 'Address TBC'),
            'address_line2' => $lead->address_line2,
            'city' => $lead->city,
            'postcode' => $lead->postcode,
            'booking_date' => $lead->preferred_date?->toDateString(),
            'arrival_window' => $lead->arrival_window instanceof ArrivalWindow
                ? $lead->arrival_window->value
                : (string) $lead->arrival_window,
            'agreed_price_pence' => (int) $agreed,
            'status' => BookingStatus::Scheduled->value,
            'internal_notes' => $notes,
        ];
    }

    public function createFromLead(QuoteRequest $lead, array $overrides = []): Booking
    {
        if ($lead->status !== QuoteRequestStatus::Won) {
            throw new InvalidArgumentException('Only won leads can be converted to bookings.');
        }

        if ($lead->bookings()->exists()) {
            throw new InvalidArgumentException('This lead already has a booking.');
        }

        return DB::transaction(function () use ($lead, $overrides): Booking {
            $attributes = array_merge($this->prefillFromLead($lead), $overrides);
            $attributes['reference'] = $this->references->next();
            $attributes['status'] = $attributes['status'] ?? BookingStatus::Scheduled->value;

            return Booking::query()->create($attributes);
        });
    }
}
