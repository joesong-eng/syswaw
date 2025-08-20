<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Handler\RotatingFileHandler; // 如果您使用 daily driver，也需要這個
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Formatter\LineFormatter; // <<=== 這是關鍵！
return [

    /*
    |--------------------------------------------------------------------------
    | Default Log Channel
    |--------------------------------------------------------------------------
    |
    | This option defines the default log channel that gets used when writing
    | messages to the logs. The name specified in this option should match
    | one of the channels defined in the "channels" configuration array.
    |
    */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
    |--------------------------------------------------------------------------
    | Deprecations Log Channel
    |--------------------------------------------------------------------------
    |
    | This option controls the log channel that should be used to log warnings
    | regarding deprecated PHP and library features. This allows you to get
    | your application ready for upcoming major versions of dependencies.
    |
    */

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Channels
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log channels for your application. Out of
    | the box, Laravel uses the Monolog PHP logging library. This gives
    | you a variety of powerful log handlers / formatters to utilize.
    |
    | Available Drivers: "single", "daily", "slack", "syslog",
    |                    "errorlog", "monolog",
    |                    "custom", "stack"
    |
    */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => ['single', 'daily'],
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
            'permissions' => 0775, // 添加這一行

        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => 3,
            'max_files' => 3, // <--- 這裡可以調整保留的檔案數量，與 days 搭配使用
            'max_size' => 1, // 可選：每個檔案的最大大小（MB），達到後會輪替
            'replace_placeholders' => true,
            'permissions' => 0775,
        ],

        'slack' => [
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'),
            'username' => 'Laravel Log',
            'emoji' => ':boom:',
            'level' => env('LOG_LEVEL', 'critical'),
            'replace_placeholders' => true,
        ],

        'papertrail' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => env('LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class),
            'handler_with' => [
                'host' => env('PAPERTRAIL_URL'),
                'port' => env('PAPERTRAIL_PORT'),
                'connectionString' => 'tls://' . env('PAPERTRAIL_URL') . ':' . env('PAPERTRAIL_PORT'),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'stderr' => [
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER'),
            'with' => [
                'stream' => 'php://stderr',
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
        'redis_cmd' => [
            // 選項二：每日日誌輪替 (推薦用於生產環境，防止檔案過大)
            'driver' => 'daily', // 使用 daily driver
            'path' => storage_path('logs/redis_cmd.log'), // 日誌檔案路徑
            // 'path' => storage_path('logs/redis_cmd-' . date('Y-m-d') . '.log'),
            'days' => 3, // 保留最近 7 天的日誌
            'level' => 'info', // 此通道的最低日誌別
            'permission' => 0775,  // 可選，文件權限
            // 重要的：定義日誌格式以控制日誌行的結構。
            // 這有助於 Laravel 和 Python 之間日誌解析的一致性。
            'formatter' => LineFormatter::class,
            'formatter_with' => [
                'format' => "[%datetime%] %message%  %context% %extra%\n",
                'dateFormat' => 'ymd His', // 標準日期時間格式
                'allowInlineLineBreaks' => true,
                'ignoreEmptyContextAndExtra' => true,
            ],
            'with' => [
                'encoding' => 'UTF-8',
            ],
            'processors' => [
                // 如果您需要向每個日誌條目添加動態數據（例如：IP 地址、當前用戶 ID），
                // 可以在這裡添加 Monolog Processors。
                // PsrLogMessageProcessor::class, // Laravel 日誌預設已包含
            ],
        ],
    ],

];
