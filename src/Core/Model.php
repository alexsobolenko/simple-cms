<?php

declare(strict_types=1);

namespace App\Core;

use App\Attribute\ORM\Column;
use App\Attribute\ORM\Table;
use App\Exception\AppException;
use App\Kernel;

abstract class Model
{
    public static function findAll(): array
    {
        $columns = [];
        $reflectionModel = new \ReflectionClass(static::class);

        $table = null;
        $alias = null;
        $attributes = $reflectionModel->getAttributes(Table::class);
        if (!empty($attributes)) {
            $table = $attributes[0]->newInstance()->name;
            $alias = substr($table, 0, 1);
            $table = '`' . $table . '` ' . $alias;
        }

        $orderBy = [];
        foreach ($reflectionModel->getProperties() as $property) {
            $attributes = $property->getAttributes(Column::class);
            if (!empty($attributes)) {
                /** @var Column $column */
                $column = $attributes[0]->newInstance();
                $columns[] = $alias . '.`' . $column->name . '`';

                if ($column->order !== null) {
                    $orderBy[] = $alias . '.`' . $column->name . '` ' . mb_strtoupper($column->order);
                }
            }
        }

        $c = implode(', ', $columns);
        $db = Kernel::db();
        $result = [];
        try {
            $db->beginTransaction();
            $ordersql = '';
            if (!empty($orderBy)) {
                $ordersql = 'ORDER BY ' . implode(', ', $orderBy);
            }

            $result = array_map(
                fn(array $item): static => static::arrayToModel($item),
                $db->findAll("SELECT {$c} FROM {$table} {$ordersql}")
            );
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw new AppException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    public static function findOne(array $params): static
    {
        $columns = [];
        $reflectionModel = new \ReflectionClass(static::class);

        $table = null;
        $alias = null;
        $attributes = $reflectionModel->getAttributes(Table::class);
        if (!empty($attributes)) {
            $table = $attributes[0]->newInstance()->name;
            $alias = substr($table, 0, 1);
            $table = '`' . $table . '` ' . $alias;
        }

        $filters = [];
        foreach ($reflectionModel->getProperties() as $property) {
            $attributes = $property->getAttributes(Column::class);
            if (!empty($attributes)) {
                /** @var Column $column */
                $column = $attributes[0]->newInstance();
                $columns[] = $alias . '.`' . $column->name . '`';

                if (array_key_exists($property->name, $params))
                $filters[] = $alias . '.`' . $column->name . '` = ' . $column->value($params[$property->name]);
            }
        }

        $c = implode(', ', $columns);
        $f = implode(' AND ', $filters);
        $db = Kernel::db();
        try {
            $db->beginTransaction();
            $filtersql = empty($filters) ? '' : "WHERE {$f}";
            $item = $db->findOne("SELECT {$c} FROM {$table} ${filtersql}");
            $result = static::arrayToModel($item);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw new AppException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    public static function arrayToModel(array $data): static
    {
        $model = new static();
        $reflectionModel = new \ReflectionClass(static::class);
        foreach ($reflectionModel->getProperties() as $property) {
            $attributes = $property->getAttributes(Column::class);
            if (!empty($attributes)) {
                /** @var Column $column */
                $column = $attributes[0]->newInstance();
                $model->{$property->name} = $column->model($data);
            }
        }

        return $model;
    }

    /**
     * @throws AppException
     */
    public function save(bool $update)
    {
        $columns = [];
        $values = [];
        $reflectionModel = new \ReflectionClass(get_class($this));

        $table = null;
        $attributes = $reflectionModel->getAttributes(Table::class);
        if (!empty($attributes)) {
            $table = $attributes[0]->newInstance()->name;
        }

        foreach ($reflectionModel->getProperties() as $property) {
            $attributes = $property->getAttributes(Column::class);
            if (!empty($attributes)) {
                /** @var Column $column */
                $column = $attributes[0]->newInstance();
                if ($update && $property->name === 'id') {
                    continue;
                }
                $columns[] = '`' . $column->name . '`';
                $values[] = $column->value($this->{$property->name});
            }
        }

        if ($update) {
            $sets = [];
            foreach (array_combine($columns, $values) as $column => $value) {
                $sets[] = "{$column} = {$value}";
            }
            $c = implode(', ', $sets);
            $v = 'WHERE `id` = "' . $this->id . '"';
        } else {
            $c = implode(', ', $columns);
            $v = implode(', ', $values);
        }

        $db = Kernel::db();
        try {
            $db->beginTransaction();
            if ($update) {
                $db->sql("UPDATE `{$table}` SET {$c} {$v}");
            } else {
                $db->sql("INSERT INTO `{$table}` ({$c}) VALUES ({$v})");
            }
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw new AppException($e->getMessage(), $e->getCode(), $e);
        }

        return $this;
    }

    public function delete(): void
    {
        $table = null;
        $reflectionModel = new \ReflectionClass(get_class($this));
        $attributes = $reflectionModel->getAttributes(Table::class);
        if (!empty($attributes)) {
            $table = $attributes[0]->newInstance()->name;
        }

        $c = null;
        $v = null;
        foreach ($reflectionModel->getProperties() as $property) {
            if ($property->name !== 'id') {
                continue;
            }

            $attributes = $property->getAttributes(Column::class);
            if (!empty($attributes)) {
                /** @var Column $column */
                $column = $attributes[0]->newInstance();
                $c = '`' . $column->name . '`';
                $v = $column->value($this->{$property->name});
            }
        }

        $db = Kernel::db();
        try {
            $db->beginTransaction();
            $db->sql("DELETE FROM `{$table}` WHERE {$c} = {$v}");
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw new AppException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
