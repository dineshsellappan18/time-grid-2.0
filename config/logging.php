<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [

    'default' => env('LOG_CHANNEL', 'stack'),

    'deprecations' => [
        'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'),
        'trace'   => false,
    ],

    'channels' => [
        'stack' => [
            'driver'            => 'stack',
            'channels'          => ['daily'],
            'ignore_exceptions' => false,
        ],

        'daily' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/laravel.log'),
            'level'  => env('LOG_LEVEL', 'debug'),
            'days'   => 14,
            'tap'    => [App\Logging\JsonFormatter::class],
        ],

        'security' => [
            'driver' => 'daily',
            'path'   => storage_path('logs/security.log'),
            'level'  => 'info',
            'days'   => 14,
            'tap'    => [App\Logging\JsonFormatter::class],
        ],

        'single' => [
            'driver'         => 'single',
            'path'           => storage_path('logs/laravel.log'),
            'level'          => env('LOG_LEVEL', 'debug'),
            'tap'            => [App\Logging\JsonFormatter::class],
            'replace_placeholders' => true,
        ],

        'syslog' => [
            'driver'   => 'syslog',
            'level'    => env('LOG_LEVEL', 'debug'),
            'facility' => LOG_USER,
            'tag'      => env('SYSLOG_APPNAME', 'timegrid'),
            'tap'      => [App\Logging\JsonFormatter::class],
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level'  => env('LOG_LEVEL', 'debug'),
            'tap'    => [App\Logging\JsonFormatter::class],
        ],

        'null' => [
            'driver'  => 'monolog',
            'handler' => NullHandler::class,
        ],

        'stderr' => [
            'driver'  => 'monolog',
            'level'   => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'with'    => [
                'stream' => 'php://stderr',
            ],
            'tap' => [App\Logging\JsonFormatter::class],
        ],
    ],

];
