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

];
