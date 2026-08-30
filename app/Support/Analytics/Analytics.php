<?php

namespace App\Support\Analytics;

/**
 * Provider-agnostic analytics event names and helpers.
 * Events are emitted to the browser queue only; no vendor ships unless configured.
 */
final class Analytics
{
    public const QUOTE_STARTED = 'quote_started';

    public const QUOTE_STEP_COMPLETED = 'quote_step_completed';

    public const QUOTE_COMPLETED = 'quote_completed';

    public const WHATSAPP_QUOTE = 'whatsapp_quote';

    public const WHATSAPP_CLICKED = 'whatsapp_clicked';

    public const PHONE_CLICKED = 'phone_clicked';

    public const SERVICE_VIEWED = 'service_viewed';

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function scriptCall(string $event, array $payload = []): string
    {
        return 'window.ngTrack && window.ngTrack('.json_encode($event).', '.json_encode($payload, JSON_UNESCAPED_SLASHES).')';
    }
}
