<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Error;
use Phalcon\Di;
use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\CommandResultInterface;

abstract class UpdateDbCommand extends DbCommand
{
    use ModelTrait;

    final protected function handle(): CommandResultInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $modelName = $this->getModelName();
        $primaryKey = $this->getPrimaryKey() != null
            ? $this->getPrimaryKey()
            : $this->getModelPrimaryKey();

        $primaryKeyValue = $this->$primaryKey;
        if (empty($primaryKeyValue))
            throw new Error('Primary key value not be null. (' . get_called_class() . ')');

        $entity = $modelName::findFirst($primaryKeyValue);
        if (!$entity)
            return CommandResult::fail('No records found');

        $entity->assign($this->getDataForModel($primaryKey));

        if (method_exists($this, 'beforeUpdate'))
        {
            $result = $this->beforeUpdate($entity);

            if (isset($result) and $result instanceof CommandResultInterface and $result->isFail())
                return $result;
        }

        if ($entity->update())
        {
            if (method_exists($this, 'afterUpdate'))
                $this->afterUpdate($entity);

            return CommandResult::success();
        }

        return CommandResult::fail($entity->getMessages());
    }

    private function getModelPrimaryKey(): ?string
    {
        $modelName = $this->getModelName();
        $model = new $modelName;
        $primaryKeys = Di::getDefault()->get('modelsMetadata')->getPrimaryKeyAttributes($model);
        return $primaryKeys[0] ?? null;
    }

    private function getDataForModel(string $primaryKey): array
    {
        $data = $this->getData();
        unset($data[$primaryKey]);

        $dataWithBuiltinType = [];
        foreach ($data as $name => $value)
            if (!is_object($value) and !is_array($value))
                $dataWithBuiltinType[$name] = $value;

        return $dataWithBuiltinType;
    }
}