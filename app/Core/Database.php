<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

class Database
{
    /** @var array<string, PDO> */
    private static array $connections = [];

    public static function connection(string $name = 'mysql'): PDO
    {
        if (isset(self::$connections[$name])) {
            return self::$connections[$name];
        }

        $config = config('database.connections.' . $name);

        if (!is_array($config)) {
            throw new \RuntimeException('Conexão de banco não configurada: ' . $name);
        }
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        self::$connections[$name] = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options']
        );

        return self::$connections[$name];
    }

    public static function beginTransaction(): void
    {
        self::connection()->beginTransaction();
    }

    public static function commit(): void
    {
        self::connection()->commit();
    }

    public static function rollBack(): void
    {
        if (self::connection()->inTransaction()) {
            self::connection()->rollBack();
        }
    }
}
