<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Error;
use Phalcon\Di;
use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\CommandResultInterface;

abstract class AbstractDeleteCommand extends AbstractCommand
{
    use ModelTrait;

    final protected function handle(): CommandResultInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $modelName = $this->getModelName();
        $primaryKey = $this->getPrimaryKey() != null
            ? $this->getPrimaryKey()
            : $this->getModelPrimaryKey($modelName);

        $primaryKeyValue = $this->$primaryKey;
        if (empty($primaryKeyValue))
            throw new Error('Primary key value not be null. (' . get_called_class() . ')');

        $entity = $modelName::findFirst($primaryKeyValue);
        if (!$entity)
            return CommandResult::fail('No records found');

        if ($entity->delete())
            return CommandResult::success();

        return CommandResult::fail($entity->getMessages());
    }

    private function getModelPrimaryKey(): ?string
    {
        $modelName = $this->getModelName();
        $model = new $modelName;
        $primaryKeys = Di::getDefault()->get('modelsMetadata')->getPrimaryKeyAttributes($model);
        return $primaryKeys[0] ?? null;
    }
}