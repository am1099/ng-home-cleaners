<?php

namespace App\Services;

use App\Models\Customer;
use App\Support\UkContactNormalizer;

/**
 * Resolve customers without silently merging conflicting identities.
 *
 * Safe matches:
 * - exact normalised email
 * - exact normalised phone when emails are compatible (same, or one side missing)
 *
 * Phone + conflicting emails → no match (caller creates a new customer).
 */
final class CustomerMatcher
{
    public function findMatch(?string $email, ?string $phone): ?Customer
    {
        $normalizedEmail = filled($email) ? UkContactNormalizer::normalizeEmail($email) : null;
        $normalizedPhone = filled($phone) ? UkContactNormalizer::normalizePhone($phone) : null;

        if ($normalizedEmail) {
            $byEmail = Customer::query()->where('email', $normalizedEmail)->first();

            if ($byEmail) {
                return $byEmail;
            }
        }

        if (! $normalizedPhone) {
            return null;
        }

        $candidates = Customer::query()
            ->where('phone_normalized', $normalizedPhone)
            ->get();

        foreach ($candidates as $candidate) {
            if ($this->emailsAreCompatible($normalizedEmail, $candidate->email)) {
                return $candidate;
            }
        }

        return null;
    }

    public function emailsAreCompatible(?string $incomingEmail, ?string $existingEmail): bool
    {
        if (blank($incomingEmail) || blank($existingEmail)) {
            return true;
        }

        return UkContactNormalizer::normalizeEmail($incomingEmail)
            === UkContactNormalizer::normalizeEmail($existingEmail);
    }
}
