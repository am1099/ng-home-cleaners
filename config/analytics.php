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
    | Google Ads tag (AW-…). Loaded on public pages. Consent Mode v2 defaults
    | advertising storage to denied, so the tag may send cookieless pings before
    | the visitor accepts. Ad cookies are written only after acceptance.
    | Set to empty to disable.
    */
    'google_ads_id' => env('GOOGLE_ADS_ID', 'AW-18369596770'),

    /*
    | GA4 measurement ID. Configured on the same gtag as Google Ads.
    | analytics_storage stays denied until cookie acceptance. Set empty to disable.
    */
    'google_analytics_id' => env('GOOGLE_ANALYTICS_ID', 'G-D6CEG5ZQRY'),

    /*
    | Google Ads conversion send_to values (AW-ID/label). Fired once per genuine
    | user-initiated WhatsApp or phone action. Consent Mode decides whether the
    | hit is cookieless or fully attributed.
    */
    'google_ads_whatsapp_send_to' => env(
        'GOOGLE_ADS_WHATSAPP_SEND_TO',
        'AW-18369596770/J-GNCIHs8LgdEOKap7dE',
    ),

    'google_ads_phone_send_to' => env(
        'GOOGLE_ADS_PHONE_SEND_TO',
        'AW-18369596770/GafGCITs8LgdEOKap7dE',
    ),

    /*
    | Offline conversion action for a confirmed booking. Must match the
    | Google Ads conversion action name exactly. Empty disables the export.
    */
    'google_ads_booked_conversion_name' => env('GOOGLE_ADS_BOOKED_CONVERSION_NAME', 'NG - Booked job'),

];
