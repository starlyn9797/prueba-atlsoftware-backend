<?php

declare(strict_types=1);

$config = [
    'driver'  => $_ENV['DB_DRIVER']  ?? 'mysql',
    'host'    => $_ENV['DB_HOST']    ?? 'localhost',
    'dbname'  => $_ENV['DB_NAME']    ?? 'contacts_api',
    'user'    => $_ENV['DB_USER']    ?? 'root',
    'pass'    => $_ENV['DB_PASS']    ?? '',
    'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
];

$dsn = "{$config['driver']}:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

return new PDO($dsn, $config['user'], $config['pass'], [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
]);
