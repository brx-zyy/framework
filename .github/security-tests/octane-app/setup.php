<?php
$pdo = new PDO(
    'mysql:host='.(getenv('DB_HOST') ?: '127.0.0.1').';port='.(getenv('DB_PORT') ?: '3306').';dbname='.(getenv('DB_DATABASE') ?: 'laravel_test').';charset=utf8mb4',
    (getenv('DB_USERNAME') ?: 'root'), (getenv('DB_PASSWORD') ?: 'password'),
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$pdo->exec('DROP TABLE IF EXISTS accounts');
$pdo->exec('CREATE TABLE accounts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, is_admin TINYINT(1) NOT NULL DEFAULT 0, observer_seen TINYINT(1) NOT NULL DEFAULT 0)');
echo "octane fixture database ready\n";
