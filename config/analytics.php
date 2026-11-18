<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    |
    | Events always queue in the browser. A third-party script is only loaded
    | after cookie consent when both enabled and a driver are set.
    |
    */

    'enabled' => (bool) env('ANALYTICS_ENABLED', false),

    /*
    | Supported drivers: null (queue only), "console", "plausible".
    */
    'driver' => env('ANALYTICS_DRIVER', 'plausible'),

    'plausible' => [
        'domain' => env('PLAUSIBLE_DOMAIN'),
        'script_url' => env('PLAUSIBLE_SCRIPT_URL', 'https://plausible.io/js/script.js'),
    ],

    /*
    | Google Ads tag (AW-…). Loaded on public pages. Advertising storage stays
    | denied until the visitor accepts the cookie banner. Set to empty to disable.
    */
    'google_ads_id' => env('GOOGLE_ADS_ID', 'AW-18369596770'),

    /*
    | Google Ads conversion send_to values (AW-ID/label). Fired once per genuine
    | user-initiated WhatsApp or phone action after advertising consent is granted.
    */
    'google_ads_whatsapp_send_to' => env(
        'GOOGLE_ADS_WHATSAPP_SEND_TO',
        'AW-18369596770/J-GNCIHs8LgdEOKap7dE',
    ),

    'google_ads_phone_send_to' => env(
        'GOOGLE_ADS_PHONE_SEND_TO',
        'AW-18369596770/GafGCITs8LgdEOKap7dE',
    ),

];
