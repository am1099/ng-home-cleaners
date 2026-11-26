<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'google' => [
        'project_id' => env('GOOGLE_CLOUD_PROJECT'),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REVIEW_REDIRECT', rtrim((string) env('APP_URL'), '/').'/google-reviews/callback'),
        'reporting_redirect' => env('GOOGLE_REPORTING_REDIRECT', rtrim((string) env('APP_URL'), '/').'/google-reporting/callback'),
        'ads_customer_id' => preg_replace('/\D/', '', (string) env('GOOGLE_ADS_CUSTOMER_ID', '')) ?: null,
        'analytics_property_id' => preg_replace('/\D/', '', (string) env('GOOGLE_ANALYTICS_PROPERTY_ID', '')) ?: null,
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
