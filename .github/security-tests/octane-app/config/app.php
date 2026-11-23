<?php
return [
    'name' => env('APP_NAME', 'Octane Security Probe'),
    'env' => env('APP_ENV', 'testing'),
    'debug' => true,
    'url' => 'http://127.0.0.1:8000',
    'timezone' => 'UTC',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA='),
    'cipher' => 'AES-256-CBC',
    'maintenance' => ['driver' => 'file'],
    'providers' => Illuminate\Support\ServiceProvider::defaultProviders()->merge([])->toArray(),
];
