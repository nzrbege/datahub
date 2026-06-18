<?php

use Illuminate\Support\Facades\Facade;

return [
    'name'     => env('APP_NAME', 'Sistem Data Keluarga'),
    'env'      => env('APP_ENV', 'production'),
    'debug'    => (bool) env('APP_DEBUG', false),
    'url'      => env('APP_URL', 'http://localhost'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
    'locale'   => env('APP_LOCALE', 'id'),
    'fallback_locale' => 'id',
    'faker_locale'    => 'id_ID',
    'key'      => env('APP_KEY'),
    'cipher'   => 'AES-256-CBC',

    // Audit log disimpan permanen (UU PDP Pasal 47)
    'audit_retention_days' => null,

    'maintenance' => ['driver' => 'file'],

    'providers' => \Illuminate\Support\ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        Spatie\Activitylog\ActivitylogServiceProvider::class,
        Mews\Captcha\CaptchaServiceProvider::class,
    ])->toArray(),

    'aliases' => Facade::defaultAliases()->merge([
        'Captcha' => Mews\Captcha\Facades\Captcha::class,
    ])->toArray(),
];
