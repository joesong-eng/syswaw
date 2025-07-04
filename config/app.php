<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    'name' => env('APP_NAME', 'Syswaw'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'https://sxs.tg25.win'),
    'asset_url' => env('ASSET_URL'),
    'locale' => 'zh-TW',
    // 'timezone' => 'UTC',
    'timezone' => 'Asia/Taipei',
    'locale' => 'en',
    'fallback_locale' => 'zh-TW',
    'fallback_locale' => 'zh-CN',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',
    'maintenance' => [
        'driver' => 'file',
        // 'store' => 'redis',
    ],
    'providers' => ServiceProvider::defaultProviders()->merge([
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class, //廣播事件
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\FortifyServiceProvider::class,
        App\Providers\JetstreamServiceProvider::class,
        Spatie\Permission\PermissionServiceProvider::class,
        SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class,
    ])->toArray(),
    'aliases' => Facade::defaultAliases()->merge([
        // 'Example' => App\Facades\Example::class,
        'Broadcast' => Illuminate\Support\Facades\Broadcast::class,
        'QrCode' => SimpleSoftwareIO\QrCode\Facades\QrCode::class,

    ])->toArray(),

];
