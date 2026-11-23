<?php
$connection = [
    'driver' => 'mysql', 'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'), 'database' => env('DB_DATABASE', 'laravel_test'),
    'username' => env('DB_USERNAME', 'root'), 'password' => env('DB_PASSWORD', 'password'),
    'unix_socket' => '', 'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci',
    'prefix' => '', 'prefix_indexes' => true, 'strict' => true,
    'engine' => null, 'options' => [],
];
return [
    'default' => 'default',
    'connections' => [
        'default' => $connection,
        'force' => $connection,
        'ordinary' => $connection,
        'quiet' => $connection,
        'observer' => $connection,
    ],
    'migrations' => ['table' => 'migrations', 'update_date_on_publish' => true],
    'redis' => [],
];
