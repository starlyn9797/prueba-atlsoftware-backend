<?php

declare(strict_types=1);

namespace App\Infrastructure\Configs;

use PDO;
use RuntimeException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(array $config): PDO
    {
        if (self::$instance === null) {
            $requiredKeys = ['driver', 'host', 'dbname', 'charset', 'user', 'pass'];

            foreach ($requiredKeys as $key) {
                if (!array_key_exists($key, $config)) {
                    throw new RuntimeException("Clave de configuración faltante: '{$key}'");
                }
            }

            $connectionString = "{$config['driver']}:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";

            self::$instance = new PDO($connectionString, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }

        return self::$instance;
    }
}
