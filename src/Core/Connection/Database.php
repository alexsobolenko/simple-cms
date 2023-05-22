<?php

declare(strict_types=1);

namespace App\Core\Connection;

use App\Core\Http\Response;
use App\Exception\Core\DatabaseException;

final class Database
{
    /** @var Database|null */
    private static ?Database $instance = null;

    /** @var \PDO */
    private \PDO $pdo;

    /**
     * @param array $config
     * @throws DatabaseException
     */
    private function __construct(array $config)
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? null;
        if ($host === null) {
            throw new DatabaseException('Host should be specified', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $port = $config['port'] ?? 3306;
        $dbname = $config['dbname'] ?? null;
        if ($dbname === null) {
            throw new DatabaseException('DB name should be specified', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        $options = $config['options'] ?? [
            \PDO::ATTR_EMULATE_PREPARES =>false,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new \PDO(
                "{$driver}:host={$host};port={$port};dbname={$dbname}",
                $config['user'],
                $config['pass'],
                $options
            );
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, $e);
        }
    }

    /**
     * @param array $config
     * @return Database
     * @throws DatabaseException
     */
    public static function getInstance(array $config): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database($config);
        }

        return self::$instance;
    }

    /**
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function inTransaction(): bool
    {
        return $this->pdo->inTransaction();
    }

    /**
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    /**
     * @param string $query
     * @return \PDOStatement
     */
    public function query(string $query): \PDOStatement
    {
        return $this->pdo->query($query);
    }

    /**
     * @param string $query
     * @return \PDOStatement
     */
    public function prepare(string $query): \PDOStatement
    {
        return $this->pdo->prepare($query);
    }

    /**
     * @param string $query
     * @return mixed
     */
    public function exec(string $query)
    {
        return $this->pdo->exec($query);
    }

    /**
     * @return mixed
     */
    public function lastInsertId(): mixed
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $query
     * @param array $args
     * @return \PDOStatement
     * @throws DatabaseException
     */
    public function run(string $query, array $args = []): \PDOStatement
    {
        try{
            if (empty($args)) {
                return $this->query($query);
            }

            $stmt = $this->prepare($query);
            $stmt->execute($args);

            return $stmt;
        } catch (\PDOException $e) {
            throw new DatabaseException($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param string $query
     * @param array $args
     * @return array
     * @throws DatabaseException
     */
    public function findOne(string $query, array $args = []): array
    {
        return $this->run($query, $args)->fetch();
    }

    /**
     * @param string $query
     * @param array $args
     * @return array
     * @throws DatabaseException
     */
    public function findAll(string $query, array $args = []): array
    {
        return $this->run($query, $args)->fetchAll();
    }

    /**
     * @param string $query
     * @param array $args
     * @throws DatabaseException
     */
    public function sql(string $query, array $args = []): void
    {
        $this->run($query, $args);
    }
}
