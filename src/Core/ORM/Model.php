<?php

declare(strict_types=1);

namespace App\Core\ORM;

use App\Attribute\ORM;
use App\Exception\Core\BaseModelException;
use App\Kernel;
use Ramsey\Uuid\Uuid;

abstract class Model
{
    /**
     * @return array
     * @throws BaseModelException
     */
    public static function findAll(): array
    {
        Kernel::db()->beginTransaction();
        try {
            $className = static::class;
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
            $columnsOrder = empty($orderBy) ? '' : 'ORDER BY ' . implode(', ', $orderBy);
            $result = array_map(
                fn(array $item): static => static::arrayToModel($item),
                Kernel::db()->findAll("SELECT {$columnsSelect} FROM {$tableName} {$columnsOrder}")
            );
            Kernel::db()->commit();
        } catch (\Throwable $e) {
            if (Kernel::db()->inTransaction()) {
                Kernel::db()->rollBack();
            }

            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * @param mixed $id
     * @return static
     * @throws BaseModelException
     */
    public static function find(mixed $id): static
    {
        return static::findOne(['id' => $id]);
    }

    /**
     * @param array $params
     * @return static
     * @throws BaseModelException
     */
    public static function findOne(array $searchParams): static
    {
        Kernel::db()->beginTransaction();
        try {
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

            $filtersql = empty($filters) ? '' : "WHERE {$columnsFilter}";
            $item = Kernel::db()->findOne("SELECT {$columnsSelect} FROM {$tableName} {$filtersql}");
            $result = static::arrayToModel($item);
            Kernel::db()->commit();
        } catch (\Throwable $e) {
            if (Kernel::db()->inTransaction()) {
                Kernel::db()->rollBack();
            }

            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }

        return $result;
    }

    /**
     * @param array $data
     * @return static
     * @throws BaseModelException
     */
    public static function arrayToModel(array $data): static
    {
        try {
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
        } catch (\Throwable $e) {
            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param bool $update
     * @return static
     * @throws BaseModelException
     */
    public function save(bool $update): static
    {
        Kernel::db()->beginTransaction();
        try {
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
                    $values[] = !$update && $column->generate !== null
                        ? $this->generateNextValue($column, $tableName)
                        : $column->value($this->{$reflectionProperty->name});
                }
            }

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
            Kernel::db()->sql($sql);
            Kernel::db()->commit();
        } catch (\Throwable $e) {
            if (Kernel::db()->inTransaction()) {
                Kernel::db()->rollBack();
            }

            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }

        return $this;
    }

    /**
     * @throws BaseModelException
     */
    public function delete(): void
    {
        Kernel::db()->beginTransaction();
        try {
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

            Kernel::db()->sql("DELETE FROM `{$tableName}` WHERE {$columnName} = {$columnValue}");
            Kernel::db()->commit();
        } catch (\Throwable $e) {
            if (Kernel::db()->inTransaction()) {
                Kernel::db()->rollBack();
            }

            throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param ORM\Column $column
     * @param string $tableName
     * @return mixed
     *
     */
    private function generateNextValue(ORM\Column $column, string $tableName): mixed
    {
        if ($column->generate === 'integer') {
            try {
                $sql = "SELECT MAX(`{$column->name}`) AS `max_value` FROM `{$tableName}`";
                $result = Kernel::db()->findOne($sql);
                $value = (int) $result['max_value'] + 1;
            } catch (\Throwable $e) {
                throw new BaseModelException($e->getMessage(), $e->getCode(), $e);
            }
        } else {
            $value = match ($column->generate) {
                'uuid1' => Uuid::uuid1()->toString(),
                'uuid2' => Uuid::uuid2(Uuid::DCE_DOMAIN_PERSON)->toString(),
                'uuid3' => Uuid::uuid3(Uuid::NAMESPACE_OID, 'simple-cms')->toString(),
                'uuid4', 'uuid' => Uuid::uuid4()->toString(),
                'uuid5' => Uuid::uuid5(Uuid::NAMESPACE_OID, 'simple-cms')->toString(),
                'uuid6' => Uuid::uuid6()->toString(),
                default => null,
            };
        }

        return $value;
    }
}
