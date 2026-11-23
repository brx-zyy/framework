<?php
return [
    'server' => 'swoole',
    'https' => false,
    'listeners' => [],
    'warm' => [],
    'flush' => [],
    'tables' => [],
    'cache' => ['rows' => 1000, 'bytes' => 10000],
    'watch' => [],
    'garbage' => 50,
    'max_execution_time' => 0,
    'state_file' => storage_path('logs/octane-server-state.json'),
    'swoole' => [
        'options' => [
            'enable_coroutine' => filter_var(env('OCTANE_COROUTINES', 'false'), FILTER_VALIDATE_BOOLEAN),
            'hook_flags' => SWOOLE_HOOK_ALL,
        ],
    ],
];
