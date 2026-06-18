<?php

use Illuminate\Support\Str;

return [
    'driver'          => env('SESSION_DRIVER', 'database'),
    'lifetime'        => env('SESSION_LIFETIME', 120),
    'expire_on_close' => false,
    'encrypt'         => true, // Wajib encrypt untuk data sensitif (UU PDP)
    'files'           => storage_path('framework/sessions'),
    'connection'      => env('SESSION_CONNECTION'),
    'table'           => 'sessions',
    'store'           => env('SESSION_STORE'),
    'lottery'         => [2, 100],
    'cookie'          => env('SESSION_COOKIE', Str::slug(env('APP_NAME', 'laravel'), '_') . '_session'),
    'path'            => '/',
    'domain'          => env('SESSION_DOMAIN'),
    'secure'          => env('SESSION_SECURE_COOKIE', false), // true di production dengan HTTPS
    'http_only'       => true,
    'same_site'       => env('SESSION_SAME_SITE', 'strict'), // strict untuk keamanan CSRF
    'partitioned'     => false,
];
