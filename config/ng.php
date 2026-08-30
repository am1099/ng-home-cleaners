<?php

return [

    /*
    |--------------------------------------------------------------------------
    | NG Home Cleaners — site configuration
    |--------------------------------------------------------------------------
    |
    | Temporary config for layout and design-system components. Replace with
    | CRM-managed settings when the Settings module is built.
    |
    */

    'name' => env('NG_BUSINESS_NAME', 'NG Home Cleaners'),

    'phone' => env('NG_PHONE', '07503651476'),

    'phone_display' => env('NG_PHONE_DISPLAY', '07503 651476'),

    'email' => env('NG_EMAIL', 'hello@nghomecleaners.co.uk'),

    'whatsapp_url' => env('NG_WHATSAPP_URL', 'https://wa.me/447503651476'),

    'hours' => env('NG_HOURS', 'Every day, 8am–7pm'),

    'service_area' => env('NG_SERVICE_AREA', 'Nottingham and surrounding areas (NG1–NG16)'),

    'trust' => [
        'google_reviews' => env('NG_TRUST_GOOGLE_REVIEWS', true),
        'insurance' => env('NG_TRUST_INSURANCE', true),
        'insurance_amount' => env('NG_TRUST_INSURANCE_AMOUNT', '£1m'),
        'dbs' => env('NG_TRUST_DBS', true),
    ],

];
