<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tracking Domain
    |--------------------------------------------------------------------------
    |
    | The base domain used for tracking URLs. This must be configured for
    | production use instead of relying on the current request host.
    |
    | Example: 'track.yourdomain.com'
    |
    | In production, this is required. If not configured, the application
    | will throw an exception to prevent incorrect tracking URLs.
    |
    */
    'domain' => env('TRACKING_DOMAIN'),

    /*
    |--------------------------------------------------------------------------
    | Tracking Code Length
    |--------------------------------------------------------------------------
    |
    | The length of randomly generated tracking codes.
    |
    */
    'code_length' => env('TRACKING_CODE_LENGTH', 10),

    /*
    |--------------------------------------------------------------------------
    | Default Retention Days
    |--------------------------------------------------------------------------
    |
    | Default number of days to retain click data if not specified in
    | user privacy settings.
    |
    */
    'default_retention_days' => env('DEFAULT_RETENTION_DAYS', 90),
];
