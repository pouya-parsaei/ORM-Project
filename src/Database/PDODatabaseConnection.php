<?php

namespace App\Database;

use PDO;
use PDOException;
use App\Exceptions\ConfigNotValidException;
use App\Contracts\DatabaseConnectionInterface;
use App\Exceptions\DatabaseConnectionException;


class PDODatabaseConnection implements DatabaseConnectionInterface
{
    protected $connection;
    protected $config;
    const REQURED_CONFIG_KEYS = [
        'driver',
        'host',
        'database',
        'dbuser',
        'dbpassword'
    ];
    public function __construct(array $config)
    {
        if (!$this->isConfigValid($config)) {
            throw new ConfigNotValidException;
        }
        $this->config = $config;
    }
    public function connect()
    {
        $dsn = $this->generateDsn($this->config);

        try {
            $this->connection = new PDO(...$dsn);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            throw new DatabaseConnectionException($e->getMessage());
        }
        return $this;
    }
    public function getConnection()
    {
        return $this->connection;
    }

    private function generateDsn(array $config)
    {
        $dsn = "{$config['driver']}:host={$config['host']};dbname={$config['database']}";
        return [$dsn, $config['dbuser'], $config['dbpassword']];
    }
    private function isConfigValid(array $config)
    {
        $matches = array_intersect(self::REQURED_CONFIG_KEYS, array_keys($config));
        return count($matches) === count(self::REQURED_CONFIG_KEYS);
    }
}
