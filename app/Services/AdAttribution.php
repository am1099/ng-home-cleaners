<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

/**
 * Remembers an ad click for the current visit, and for 90 days only after
 * the visitor has accepted cookies. Click IDs are never written to a
 * persistent cookie before that choice.
 */
final class AdAttribution
{
    public const SESSION_KEY = 'ad_attribution';

    public const COOKIE = 'ng_attribution';

    public const CONSENT_COOKIE = 'ng_cookie_consent';

    public const MAX_AGE_DAYS = 90;

    public function capture(Request $request): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $touch = $this->touchFromQuery($request);
        $stored = $this->readStored($request->session()->get(self::SESSION_KEY));

        if ($touch !== null) {
            $stored['first'] ??= $touch;
            $stored['latest'] = $touch;
            $request->session()->put(self::SESSION_KEY, $stored);
        }

        $this->syncCookie($request, $stored);
    }

    /**
     * Attribution that may be saved on a quote. Null unless cookies were accepted.
     *
     * @return array{first: array<string, mixed>, latest: array<string, mixed>}|null
     */
    public function forQuote(?Request $request = null): ?array
    {
        $request ??= request();

        if (! $this->consentGranted($request)) {
            return null;
        }

        $stored = $this->readStored($request->hasSession() ? $request->session()->get(self::SESSION_KEY) : null);

        if ($stored['latest'] === null) {
            $stored = $this->readStored($this->readCookie($request));
        }

        $stored = $this->fresh($stored);

        return $stored['latest'] === null && $stored['first'] === null ? null : $stored;
    }

    /**
     * @param  array<string, mixed>|null  $attribution
     */
    public function summary(?array $attribution): ?string
    {
        $click = $this->exportClick($attribution);

        if ($click === null) {
            return null;
        }

        return strtoupper($click['field']).' '.$click['value'];
    }

    /**
     * The single Google click identifier to upload, preferring gclid.
     *
     * @param  array<string, mixed>|null  $attribution
     * @return array{field: string, value: string, clicked_at: string}|null
     */
    public function exportClick(?array $attribution, ?\DateTimeInterface $now = null): ?array
    {
        if (! is_array($attribution)) {
            return null;
        }

        $now ??= now();
        $stored = $this->fresh($this->readStored($attribution), $now);

        foreach (['latest', 'first'] as $slot) {
            $touch = $stored[$slot];

            if (! is_array($touch)) {
                continue;
            }

            foreach (['gclid', 'gbraid', 'wbraid'] as $field) {
                $value = $touch[$field] ?? null;

                if (is_string($value) && $value !== '') {
                    return [
                        'field' => $field,
                        'value' => $value,
                        'clicked_at' => (string) ($touch['clicked_at'] ?? ''),
                    ];
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function touchFromQuery(Request $request): ?array
    {
        $touch = [
            'gclid' => $this->clickId($request->query('gclid')),
            'gbraid' => $this->clickId($request->query('gbraid')),
            'wbraid' => $this->clickId($request->query('wbraid')),
            'fbclid' => $this->clickId($request->query('fbclid')),
            'utm_source' => $this->utm($request->query('utm_source')),
            'utm_medium' => $this->utm($request->query('utm_medium')),
            'utm_campaign' => $this->utm($request->query('utm_campaign')),
            'utm_term' => $this->utm($request->query('utm_term')),
            'utm_content' => $this->utm($request->query('utm_content')),
            'landing_page' => $this->landingPage($request),
            'clicked_at' => now()->toIso8601String(),
        ];

        $touch = array_filter($touch, fn (mixed $value): bool => filled($value));

        $hasSignal = collect(['gclid', 'gbraid', 'wbraid', 'fbclid', 'utm_source', 'utm_medium', 'utm_campaign'])
            ->contains(fn (string $key): bool => isset($touch[$key]));

        return $hasSignal ? $touch : null;
    }

    private function clickId(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim((string) $value);

        if (preg_match('/^[A-Za-z0-9._-]{8,255}$/', $value) !== 1) {
            return null;
        }

        return $value;
    }

    private function utm(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim(strip_tags((string) $value));
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value) ?? '';

        if ($value === '' || strlen($value) > 120) {
            return null;
        }

        return $value;
    }

    private function landingPage(Request $request): string
    {
        $uri = $request->getRequestUri();

        return substr($uri, 0, 500);
    }

    /**
     * @return array{first: array<string, mixed>|null, latest: array<string, mixed>|null}
     */
    private function readStored(mixed $value): array
    {
        if (! is_array($value)) {
            return ['first' => null, 'latest' => null];
        }

        if (isset($value['latest']) || isset($value['first'])) {
            return [
                'first' => is_array($value['first'] ?? null) ? $value['first'] : null,
                'latest' => is_array($value['latest'] ?? null) ? $value['latest'] : null,
            ];
        }

        return ['first' => $value, 'latest' => $value];
    }

    /**
     * @param  array{first: array<string, mixed>|null, latest: array<string, mixed>|null}  $stored
     * @return array{first: array<string, mixed>|null, latest: array<string, mixed>|null}
     */
    private function fresh(array $stored, ?\DateTimeInterface $now = null): array
    {
        $now ??= now();

        foreach (['first', 'latest'] as $slot) {
            $touch = $stored[$slot];

            if (! is_array($touch)) {
                $stored[$slot] = null;

                continue;
            }

            $clickedAt = strtotime((string) ($touch['clicked_at'] ?? ''));

            if ($clickedAt === false || $clickedAt < $now->getTimestamp() - (self::MAX_AGE_DAYS * 86400)) {
                $stored[$slot] = null;
            }
        }

        return $stored;
    }

    /**
     * @param  array{first: array<string, mixed>|null, latest: array<string, mixed>|null}  $stored
     */
    private function syncCookie(Request $request, array $stored): void
    {
        if ($this->consentDeclined($request)) {
            Cookie::queue(Cookie::forget(self::COOKIE));

            return;
        }

        if (! $this->consentGranted($request) || ($stored['latest'] === null && $stored['first'] === null)) {
            return;
        }

        Cookie::queue(cookie(
            self::COOKIE,
            json_encode($this->fresh($stored), JSON_THROW_ON_ERROR),
            self::MAX_AGE_DAYS * 24 * 60,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            SymfonyCookie::SAMESITE_LAX,
        ));
    }

    private function readCookie(Request $request): mixed
    {
        $raw = $request->cookie(self::COOKIE);

        if (! is_string($raw) || $raw === '') {
            return null;
        }

        try {
            $decoded = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }

    private function consentGranted(Request $request): bool
    {
        return $request->cookie(self::CONSENT_COOKIE) === 'accepted';
    }

    private function consentDeclined(Request $request): bool
    {
        return $request->cookie(self::CONSENT_COOKIE) === 'declined';
    }
}
