<?php

return [
    'driver'  => $_ENV['DB_DRIVER'] ?? 'mysql',
    'host'    => $_ENV['DB_HOST']  ?? 'localhost',
    'dbname'  => $_ENV['DB_NAME']  ?? 'contacts_api',
    'user'    => $_ENV['DB_USER']  ?? 'root',
    'pass'    => $_ENV['DB_PASS']  ?? '',
    'charset' => $_ENV['DB_CHARSET']  ?? 'utf8mb4',
];
