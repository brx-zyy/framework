<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
Illuminate\Support\Facades\DB::statement('DROP TABLE IF EXISTS accounts');
Illuminate\Support\Facades\DB::statement('CREATE TABLE accounts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, is_admin TINYINT(1) NOT NULL DEFAULT 0, observer_seen TINYINT(1) NOT NULL DEFAULT 0)');
echo "octane fixture database ready\n";
