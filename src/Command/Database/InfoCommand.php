<?php

declare(strict_types=1);

namespace App\Command\Database;

use App\Attribute\Command\Command;
use App\Core\Controller\AbstractCommand;
use App\Exception\BaseException;
use App\Kernel;
use App\Util\ArrayUtils;

#[Command(
    name: 'database:info',
    description: 'Get database info'
)]
class InfoCommand extends AbstractCommand
{
    /**
     * @param array $arguments
     * @param array $options
     * @return int
     */
    public function run(array $arguments = [], array $options = []): int
    {
        try {
            $tables = ArrayUtils::map(
                Kernel::db()->findAll('SHOW TABLES;'),
                static fn($item) => array_values($item)[0]
            );
            $result = [];
            $columnWidth = 17;
            foreach ($tables as $table) {
                $tableResult = [
                    [
                        str_pad('Field', $columnWidth - 1, ' ', STR_PAD_LEFT) . ' ',
                        str_pad('Type', $columnWidth - 1, ' ', STR_PAD_LEFT) . ' ',
                        str_pad('Null', $columnWidth - 1, ' ', STR_PAD_LEFT) . ' ',
                        str_pad('Key', $columnWidth - 1, ' ', STR_PAD_LEFT) . ' ',
                        str_pad('Default', $columnWidth - 1, ' ', STR_PAD_LEFT) . ' ',
                        str_pad('Extra', $columnWidth - 1, ' ', STR_PAD_LEFT) . ' ',
                    ],
                ];

                $fields = Kernel::db()->findAll("DESCRIBE `{$table}`;");
                foreach ($fields as $field) {
                    $line = [];
                    foreach ($field as $item) {
                        $line[] = str_pad((string) $item, $columnWidth - 1, ' ', STR_PAD_LEFT) . ' ';
                    }
                    $tableResult[] = $line;
                }
                $result[] = PHP_EOL . "Table info \"{$table}\"" . PHP_EOL . PHP_EOL . ArrayUtils::printTable($tableResult) . PHP_EOL;
            }
            echo implode(PHP_EOL . "----------" . PHP_EOL, $result);
        } catch (BaseException) {
            return self::EXIT_ERROR;
        }

        return self::EXIT_OK;
    }
}
