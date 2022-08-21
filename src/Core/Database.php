<?php

declare(strict_types=1);

namespace App\Core;

use App\Exception\AppException;

final class Database
{
    /**
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * @var \PDO
     */
    private \PDO $pdo;

    /**
     * @param array $config
     * @throws AppException
     */
    private function __construct(array $config)
    {
        $defaultOptions = [
            \PDO::ATTR_EMULATE_PREPARES =>false,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        ];

        try {
            $this->pdo = new \PDO(
                sprintf(
                    '%s:host=%s;port=%s;dbname=%s',
                    $config['driver'] ?? 'mysql',
                    $config['host'],
                    $config['port'] ?? 3306,
                    $config['dbname']
                ),
                $config['user'],
                $config['pass'],
                $config['options'] ?? $defaultOptions
            );
        } catch (\PDOException $e) {
            throw new AppException($e->getMessage(), 500);
        }
    }

    /**
     * @param array $config
     * @return Database
     * @throws AppException
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
     * @param string $stmt
     * @return \PDOStatement
     */
    public function query(string $stmt): \PDOStatement
    {
        return $this->pdo->query($stmt);
    }

    /**
     * @param string $stmt
     * @return \PDOStatement
     */
    public function prepare(string $stmt): \PDOStatement
    {
        return $this->pdo->prepare($stmt);
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
     * @return string
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * @param string $query
     * @param array $args
     * @return \PDOStatement
     * @throws AppException
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
            throw new AppException($e->getMessage(), 500);
        }
    }

    /**
     * @param string $query
     * @param array $args
     * @return array
     * @throws AppException
     */
    public function findOne(string $query, array $args = []): array
    {
        return $this->run($query, $args)->fetch();
    }

    /**
     * @param string $query
     * @param array $args
     * @return array
     * @throws AppException
     */
    public function findAll(string $query, array $args = []): array
    {
        return $this->run($query, $args)->fetchAll();
    }

    /**
     * @param string $query
     * @param array $args
     * @throws AppException
     */
    public function sql(string $query, array $args = []): void
    {
        $this->run($query, $args);
    }
}
