<?php

namespace App\Services;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Customer;
use App\Support\UkContactNormalizer;

final class BookingConfirmationMatcher
{
    /**
     * @return array{customer: ?Customer, booking: ?Booking}
     */
    public function match(string $fullName, string $address): array
    {
        $customer = $this->matchCustomer($fullName, $address);

        if (! $customer) {
            return ['customer' => null, 'booking' => null];
        }

        $scheduled = $customer->bookings()
            ->where('status', BookingStatus::Scheduled)
            ->get();

        return [
            'customer' => $customer,
            'booking' => $scheduled->count() === 1 ? $scheduled->first() : null,
        ];
    }

    private function matchCustomer(string $fullName, string $address): ?Customer
    {
        $name = mb_strtolower(trim(preg_replace('/\s+/', ' ', $fullName) ?? ''));
        $postcode = $this->extractPostcode($address);

        if ($name === '' || $postcode === null) {
            return null;
        }

        $matches = Customer::query()
            ->get()
            ->filter(function (Customer $customer) use ($name, $postcode): bool {
                $customerName = mb_strtolower(trim(preg_replace('/\s+/', ' ', $customer->fullName()) ?? ''));

                if ($customerName !== $name || blank($customer->postcode)) {
                    return false;
                }

                return UkContactNormalizer::normalizePostcode($customer->postcode) === $postcode;
            })
            ->values();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function extractPostcode(string $address): ?string
    {
        if (! preg_match('/\b([A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2})\b/i', $address, $matches)) {
            return null;
        }

        return UkContactNormalizer::normalizePostcode($matches[1]);
    }
}
