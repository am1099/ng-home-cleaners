<?php

namespace App\Services;

use App\Models\SiteEvent;
use Illuminate\Http\Request;

final class SiteEventRecorder
{
    public const PAGE_VIEW = 'page_view';

    public const WHATSAPP_OPENED = 'whatsapp_opened';

    public const PHONE_CLICKED = 'phone_clicked';

    public const FORM_STARTED = 'form_started';

    /**
     * @param  array<string, mixed>  $payload
     */
    public function record(array $payload, Request $request): bool
    {
        $event = $payload['event'] ?? null;

        if (! is_string($event) || ! in_array($event, $this->allowed(), true)) {
            return false;
        }

        $needsConsent = in_array($event, [self::PAGE_VIEW, self::FORM_STARTED], true);

        if ($needsConsent && $request->cookie(AdAttribution::CONSENT_COOKIE) !== 'accepted') {
            return false;
        }

        $path = $this->path($payload['path'] ?? null);

        if ($path === null) {
            return false;
        }

        SiteEvent::query()->create([
            'occurred_at' => now(),
            'event' => $event,
            'path' => $path,
            'source' => $this->source($payload, $request),
            'medium' => $this->short($payload['utm_medium'] ?? null, 80),
            'campaign' => $this->short($payload['utm_campaign'] ?? null, 120),
            'device' => $this->device($request),
            'visitor_key' => $this->visitor($payload['visitor'] ?? null, $request),
            'from_ads' => (bool) ($payload['ads'] ?? false),
        ]);

        return true;
    }

    /**
     * @return list<string>
     */
    private function allowed(): array
    {
        return [
            self::PAGE_VIEW,
            self::WHATSAPP_OPENED,
            self::PHONE_CLICKED,
            self::FORM_STARTED,
        ];
    }

    private function path(mixed $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $path = strtok($path, '?') ?: '';
        $path = '/'.ltrim($path, '/');

        if (strlen($path) > 300 || preg_match('/\s/', $path) === 1) {
            return null;
        }

        return $path;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function source(array $payload, Request $request): string
    {
        if ((bool) ($payload['ads'] ?? false)) {
            return 'Google Ads';
        }

        $utm = $this->short($payload['utm_source'] ?? null, 80);

        if ($utm !== null) {
            return $utm;
        }

        $referrer = $payload['referrer'] ?? null;

        if (is_string($referrer) && $referrer !== '') {
            $host = parse_url($referrer, PHP_URL_HOST);

            if (is_string($host) && $host !== '' && ! str_contains($host, $request->getHost())) {
                return substr($host, 0, 80);
            }
        }

        return 'Direct';
    }

    private function short(mixed $value, int $limit): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $value = trim(strip_tags((string) $value));
        $value = preg_replace('/[\x00-\x1F\x7F]/', '', $value) ?? '';

        if ($value === '') {
            return null;
        }

        return substr($value, 0, $limit);
    }

    private function device(Request $request): string
    {
        $agent = strtolower((string) $request->userAgent());

        if (str_contains($agent, 'ipad') || str_contains($agent, 'tablet')) {
            return 'tablet';
        }

        if (str_contains($agent, 'mobile') || str_contains($agent, 'iphone') || str_contains($agent, 'android')) {
            return 'mobile';
        }

        return 'desktop';
    }

    private function visitor(mixed $value, Request $request): ?string
    {
        if ($request->cookie(AdAttribution::CONSENT_COOKIE) !== 'accepted') {
            return null;
        }

        if (! is_string($value) || preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) !== 1) {
            return null;
        }

        return strtolower($value);
    }
}
