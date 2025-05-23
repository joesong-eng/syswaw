<?php

return [
    'default' => env('BROADCAST_DRIVER', 'reverb'),
    'connections' => [
        'reverb' => [
            'driver' => 'pusher',
            'app_id' => env('REVERB_APP_ID'),
            'key' => env('REVERB_APP_KEY'),
            'secret' => env('REVERB_APP_SECRET'),
            'options' => [
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 6001),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'encrypted' => true,
                'useTLS' => env('REVERB_SCHEME', 'https') === 'https',//trie  or false
                'path' => env('REVERB_PATH', ''),
            ],
        ],
        'log' => [
            'driver' => 'log',
        ],
        'null' => [
            'driver' => 'null',
        ],
    ],
];

        // 'pusher' => [
        //     'driver' => 'pusher',
        //     'key' => env('PUSHER_APP_KEY'),
        //     'secret' => env('PUSHER_APP_SECRET'),
        //     'app_id' => env('PUSHER_APP_ID'),
        //     'options' => [
        //         'cluster' => env('PUSHER_APP_CLUSTER'),
        //         'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
        //         'encrypted' => true,
        //         'host' => env('PUSHER_HOST', 'api-'.env('PUSHER_APP_CLUSTER', 'mt1').'.pusher.com'),
        //         'port' => env('PUSHER_PORT', 443),
        //         'scheme' => env('PUSHER_SCHEME', 'https'),
        //     ],
        //     'client_options' => [],
        // ],