<?php

declare(strict_types=1);

namespace App\Core;

final class Database
{
    /**
     * @var Database|null
     */
    private static ?Database $instance = null;

    /**
     * @param array $config
     */
    private function __construct(
        public array $config
    ) {}

    /**
     * @param array $config
     * @return Database
     */
    public static function getInstance(array $config): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database($config);
        }

        return self::$instance;
    }
}
