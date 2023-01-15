<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Error;
use YG\Phalcon\Cqrs\Command\CommandResult;

abstract class AbstractDeleteDbCommand extends AbstractDbCommand
{
    private string
        $modelClass,
        $primaryField;

    protected function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    protected function setPrimaryField(string $primaryField): void
    {
        $this->primaryField = $primaryField;
    }

    final public function execute(): CommandResult
    {
        $modelClass = $this->modelClass;
        if (!class_exists($modelClass))
            throw new Error('Model not found: ' . $modelClass);

        $primaryField = $this->primaryField;
        if ($primaryField == '')
            throw new Error('Primary field not found');

        $data = $this->getData();

        $primaryFieldValue = $data[$primaryField] ?? null;
        if ($primaryFieldValue == null)
            throw new Error('Primary field value is not set');
        unset($data[$primaryField]);

        $entity = $modelClass::findFirst($primaryFieldValue);
        if (!$entity)
            return CommandResult::fail('Entity not found');

        $entity->assign($data);

        $this->beforeExecute($entity);

        if ($entity->delete())
        {
            $this->afterExecute($entity);
            return CommandResult::success($primaryFieldValue);
        }

        return CommandResult::fail($entity->getMessages());
    }

    protected function beforeExecute($entity): void
    {
    }

    protected function afterExecute($entity): void
    {
    }
}