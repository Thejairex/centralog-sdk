<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Master switch. When disabled, capture() does nothing. Useful to turn
    | off reporting per environment without removing the integration.
    |
    */

    'enabled' => env('CENTRALOG_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Endpoint
    |--------------------------------------------------------------------------
    |
    | Full URL of your Centralog ingest endpoint, e.g.
    | https://centralog.tudominio.com/api/v1/ingest/events
    |
    */

    'endpoint' => env('CENTRALOG_ENDPOINT'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Project API key generated in Centralog (Projects → API Keys).
    | Sent as Authorization: Bearer <key>.
    |
    */

    'api_key' => env('CENTRALOG_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | Environment slug reported with each event (e.g. production, staging).
    | Defaults to the application environment.
    |
    */

    'environment' => env('CENTRALOG_ENVIRONMENT', env('APP_ENV', 'production')),

    /*
    |--------------------------------------------------------------------------
    | Release
    |--------------------------------------------------------------------------
    |
    | Release identifier attached to each event (e.g. 2026.09.30.1).
    |
    */

    'release' => env('CENTRALOG_RELEASE'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Seconds to wait for Centralog before giving up. Kept short on
    | purpose: reporting must never slow down your application.
    |
    */

    'timeout' => (int) env('CENTRALOG_TIMEOUT', 2),

];
