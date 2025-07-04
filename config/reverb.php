<?php
// <!-- config/reverb.php -->
return [
    'default' => env('REVERB_SERVER', 'reverb'),
    'servers' => [
        'reverb' => [
            'host' => env('REVERB_SERVER_HOST', '127.0.0.1'),
            'port' => env('REVERB_SERVER_PORT', 6001),
            'path' => env('REVERB_SERVER_PATH', ''),
            'hostname' => env('REVERB_HOST'),
            'options' => [
                'tls' => [],
            ],
            'max_request_size' => env('REVERB_MAX_REQUEST_SIZE', 10_000),
            'scaling' => [
                'enabled' => env('REVERB_SCALING_ENABLED', false),
                'channel' => env('REVERB_SCALING_CHANNEL', 'reverb'),
                'server' => [
                    'url' => env('REDIS_URL'),
                    'host' => env('REDIS_HOST', '127.0.0.1'),
                    'port' => env('REDIS_PORT', '6379'),
                    'username' => env('REDIS_USERNAME'),
                    'password' => env('REDIS_PASSWORD'),
                    'database' => env('REDIS_DB', '0'),
                    'timeout' => env('REDIS_TIMEOUT', 60),
                ],
            ],
            'pulse_ingest_interval' => env('REVERB_PULSE_INGEST_INTERVAL', 15),
            'telescope_ingest_interval' => env('REVERB_TELESCOPE_INGEST_INTERVAL', 15),
            'app_path' => 'app',

        ],

    ],
    'apps' => [
        'provider' => 'config',
        'apps' => [
            [
                'key' => env('REVERB_APP_KEY'),
                'secret' => env('REVERB_APP_SECRET'),
                'app_id' => env('REVERB_APP_ID'),
                'name' => env('APP_NAME'),      // 添加应用名称
                // 'broadcasting' => [                        // 新增自动广播配置
                //     'reverb_tcpScheduleResponse' => [
                //         'driver' => 'reverb',
                //         'auto_broadcast' => true          // 自动广播 Redis 变更
                //     ]
                // ],
                'options' => [
                    'host' => env('REVERB_HOST'),
                    // 'port' => env('REVERB_SERVER_PORT', 6001), // 更改這裡

                    // 這個 port 是 Reverb "認為" 前端連接的端口，透過 Nginx 代理，前端實際連接的是 443
                    'port' => env('VITE_REVERB_PORT', 443), // 這裡建議使用 VITE_REVERB_PORT 或直接寫 443
                    'scheme' => env('REVERB_SCHEME', 'https'),
                    'useTLS' => env('REVERB_SCHEME', 'https') === 'https',
                ],
                // 'allowed_origins' => ['*'],
                'allowed_origins' => [
                    'sxs.tg25.win',
                    '127.0.0.1',
                    'localhost',
                    'https://sxs.tg25.win',  // 前端 Origin
                    'http://127.0.0.1',      // Laravel 本地請求
                    'http://localhost',      // 如果用 artisan serve
                    'https://sxs.tg25.win/*',  // 前端 Origin
                    'http://127.0.0.1/*',      // Laravel 本地請求
                    'http://localhost/*',      // 如果用 artisan serve
                ],
                // 'broadcasting' => [                        // 新增自动广播配置
                //     'reverb_tcpScheduleResponse' => [
                //         'driver' => 'reverb',
                //         'auto_broadcast' => true          // 自动广播 Redis 变更
                //     ]
                // ],
                'ping_interval' => env('REVERB_APP_PING_INTERVAL', 60),
                'activity_timeout' => env('REVERB_APP_ACTIVITY_TIMEOUT', 30),
                'max_message_size' => env('REVERB_APP_MAX_MESSAGE_SIZE', 10_000),
            ],
        ],

    ],

];
