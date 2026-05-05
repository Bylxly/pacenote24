<?php
declare(strict_types=1);

return [

    'app' => [
        'env' => 'development',
        'debug' => true,
        'timezone' => 'Europe/Berlin',
    ],

    'database' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'dbname' => 'rallye_notes',
        'charset' => 'utf8mb4',
        'username' => 'root',
        'password' => '',
    ],

    'session' => [
        'name' => 'pacenote24_session',
        'timeout_seconds' => 1800, // 30 Min
    ],

    'api' => [
        'response_format' => 'json',
    ],

    'paths' => [
        'root' => dirname(__DIR__),
        'logs' => dirname(__DIR__) . '/storage/logs',
    ],

];