<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Analytics
    |--------------------------------------------------------------------------
    |
    | Events are always queued in the browser for debugging. A tracking
    | provider is only initialised when both enabled and a driver is set.
    |
    */

    'enabled' => (bool) env('ANALYTICS_ENABLED', false),

    /*
    | Supported drivers: null (queue only), "console" (dev logging).
    | Add vendor adapters later without changing event call sites.
    */
    'driver' => env('ANALYTICS_DRIVER'),

];
