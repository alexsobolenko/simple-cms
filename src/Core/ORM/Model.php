<?php

declare(strict_types=1);

namespace App\Core\ORM;

use App\Attribute\ORM;
use App\Exception\Core\BaseModelException;
use App\Kernel;

abstract class Model
{
    /**
     * Find all entries of model
     *
     * @return array
     *
     * @throws BaseModelException
     */
    public static function findAll(): array
    {
        $className = static::class;
        $db = Kernel::db();
        $result = [];

        $reflectionModel = new \ReflectionClass($className);
        $reflectionModelAttributes = $reflectionModel->getAttributes(ORM\Table::class);
        if (empty($reflectionModelAttributes)) {
            throw new BaseModelException("Table does not specified for model: {$className}");
        }

        $tableName = $reflectionModelAttributes[0]->newInstance()->name;
        $tableAlias = substr($tableName, 0, 1);
        $tableName = "`{$tableName}` $tableAlias";

        $columns = [];
        $orderBy = [];
        foreach ($reflectionModel->getProperties() as $reflectionProperty) {
            $reflectionPropertyAttributes = $reflectionProperty->getAttributes(ORM\Column::class);
            if (!empty($reflectionPropertyAttributes)) {
                $column = $reflectionPropertyAttributes[0]->newInstance();
                $columns[] = "{$tableAlias}.`{$column->name}`";

                if ($column->order !== null) {
                    $columnOrder = mb_strtoupper($column->order);
                    $orderBy[] = "{$tableAlias}.`{$column->name}` {$columnOrder}";
                }
            }
        }

        $columnsSelect = implode(', ', $columns);
        try {
            $db->beginTransaction();
            $columnsOrder = empty($orderBy) ? '' : 'ORDER BY ' . implode(', ', $orderBy);
            $result = array_map(
                fn(array $item): static => static::arrayToModel($item),
                $db->findAll("SELECT {$columnsSelect} FROM {$tableName} {$columnsOrder}")
            );
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * Find one entry by id
     *
     * @param mixed $id
     *
     * @return static
     *
     * @throws BaseModelException
     */
    public static function find(mixed $id): static
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * Find one entry of model
     *
     * @param array $params
     *
     * @return static
     *
     * @throws BaseModelException
     */
    public static function findOne(array $searchParams): static
    {
        $className = static::class;

        $reflectionModel = new \ReflectionClass($className);
        $reflectionModelAttributes = $reflectionModel->getAttributes(ORM\Table::class);
        if (empty($reflectionModelAttributes)) {
            throw new BaseModelException("Table does not specified for model: {$className}");
        }

        $tableName = $reflectionModelAttributes[0]->newInstance()->name;
        $tableAlias = substr($tableName, 0, 1);
        $tableName = "`{$tableName}` $tableAlias";

        $columns = [];
        $filters = [];
        foreach ($reflectionModel->getProperties() as $reflectionProperty) {
            $attributes = $reflectionProperty->getAttributes(ORM\Column::class);
            if (!empty($attributes)) {
                $column = $attributes[0]->newInstance();
                $columns[] = "{$tableAlias}.`{$column->name}`";

                if (array_key_exists($reflectionProperty->name, $searchParams)) {
                    $columnValue = $column->value($searchParams[$reflectionProperty->name]);
                    $filters[] = "{$tableAlias}.`{$column->name}` = {$columnValue}";
                }
            }
        }

        $columnsSelect = implode(', ', $columns);
        $columnsFilter = implode(' AND ', $filters);
        $db = Kernel::db();
        try {
            $db->beginTransaction();
            $filtersql = empty($filters) ? '' : "WHERE {$columnsFilter}";
            $item = $db->findOne("SELECT {$columnsSelect} FROM {$tableName} {$filtersql}");
            $result = static::arrayToModel($item);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * Resolve array data to model properties
     *
     * @param array $data
     *
     * @return static
     *
     * @throws BaseModelException
     */
    public static function arrayToModel(array $data): static
    {
        $className = static::class;
        $reflectionModel = new \ReflectionClass($className);

        $constructorArgumentNames = array_map(
            static fn($rp): string => $rp->name,
            $reflectionModel->getConstructor()->getParameters()
        );
        $constructorArguments = [];
        foreach ($reflectionModel->getProperties() as $reflectionProperty) {
            if (!in_array($reflectionProperty->name, $constructorArgumentNames, true)) {
                continue;
            }
            $reflectionPropertyAttributes = $reflectionProperty->getAttributes(ORM\Column::class);
            if (!empty($reflectionPropertyAttributes)) {
                $column = $reflectionPropertyAttributes[0]->newInstance();
                $constructorArguments[$reflectionProperty->name] = $column->model($data);
            }
        }

        $model = new static(...$constructorArguments);
        foreach ($reflectionModel->getProperties() as $reflectionProperty) {
            $reflectionPropertyAttributes = $reflectionProperty->getAttributes(ORM\Column::class);
            if (!empty($reflectionPropertyAttributes)) {
                $column = $reflectionPropertyAttributes[0]->newInstance();
                $model->{$reflectionProperty->name} = $column->model($data);
            }
        }

        return $model;
    }

    /**
     * Save (create or update) model data to db
     *
     * @param bool $update
     *
     * @return static
     *
     * @throws BaseModelException
     */
    public function save(bool $update): static
    {
        $className = get_class($this);

        $columns = [];
        $values = [];

        $reflectionModel = new \ReflectionClass($className);
        $reflectionModelAttributes = $reflectionModel->getAttributes(ORM\Table::class);
        if (empty($reflectionModelAttributes)) {
            throw new BaseModelException("Table does not specified for model: {$className}");
        }
        $tableName = $reflectionModelAttributes[0]->newInstance()->name;

        $preClasses = $update ? [ORM\PreUpdate::class] : [ORM\PreCreate::class];
        $preMethods = [];
        foreach ($reflectionModel->getMethods() as $reflectionMethod) {
            foreach ($preClasses as $preClass) {
                $reflectionMethodAttributes = $reflectionMethod->getAttributes($preClass);
                if (!empty($reflectionMethodAttributes)) {
                    $preMethods[] = $reflectionMethod->name;
                }
            }
        }
        foreach ($preMethods as $preMethod) {
            $this->{$preMethod}();
        }

        foreach ($reflectionModel->getProperties() as $reflectionProperty) {
            $reflectionPropertyAttributes = $reflectionProperty->getAttributes(ORM\Column::class);
            if (!empty($reflectionPropertyAttributes)) {
                $column = $reflectionPropertyAttributes[0]->newInstance();
                if ($update && $reflectionProperty->name === 'id') {
                    continue;
                }
                $columns[] = "`{$column->name}`";
                $values[] = $column->value($this->{$reflectionProperty->name});
            }
        }

        $db = Kernel::db();
        try {
            $db->beginTransaction();
            if ($update) {
                $sets = [];
                foreach (array_combine($columns, $values) as $column => $value) {
                    $sets[] = "{$column} = {$value}";
                }
                $columnsSet = implode(', ', $sets);
                $sql = "UPDATE `{$tableName}` SET {$columnsSet} WHERE `id` = '{$this->id}'";
            } else {
                $columnsList = implode(', ', $columns);
                $columnsValues = implode(', ', $values);
                $sql = "INSERT INTO `{$tableName}` ({$columnsList}) VALUES ({$columnsValues})";
            }
            $db->sql($sql);
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * Delete model data from db
     *
     * @throws BaseModelException
     */
    public function delete(): void
    {
        $className = get_class($this);

        $reflectionModel = new \ReflectionClass($className);
        $reflectionModelAttributes = $reflectionModel->getAttributes(ORM\Table::class);
        if (empty($reflectionModelAttributes)) {
            throw new BaseModelException("Table does not specified for model: {$className}");
        }
        $tableName = $reflectionModelAttributes[0]->newInstance()->name;

        $columnName = null;
        $columnValue = null;
        foreach ($reflectionModel->getProperties() as $property) {
            if ($property->name !== 'id') {
                continue;
            }

            $attributes = $property->getAttributes(ORM\Column::class);
            if (!empty($attributes)) {
                $column = $attributes[0]->newInstance();
                $columnName = "`{$column->name}`";
                $columnValue = $column->value($this->{$property->name});
            }
        }

        $db = Kernel::db();
        try {
            $db->beginTransaction();
            $db->sql("DELETE FROM `{$tableName}` WHERE {$columnName} = {$columnValue}");
            $db->commit();
        } catch (\Throwable $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }

            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
