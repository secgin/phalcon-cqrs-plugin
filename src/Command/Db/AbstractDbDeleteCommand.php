<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Phalcon\Exception;
use YG\Phalcon\Cqrs\Command\Result;
use YG\Phalcon\Cqrs\Command\ResultInterface;

abstract class AbstractDbDeleteCommand extends AbstractDbCommand
{
    use ModelTrait;

    final protected function execute(): ResultInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $this->isValid();

        $modelClass = $this->getModelName();
        $primaryKey = $this->getPrimaryKey();
        $primaryKeyValue = $this->$primaryKey;

        $entity = $modelClass::findFirst($primaryKeyValue);
        if (!$entity)
            return Result::fail('Entity not found');

        if (method_exists($this, 'beforeExecute'))
            $this->beforeExecute($entity);

        if ($entity->delete())
        {
            if (method_exists($this, 'afterExecute'))
                $this->afterExecute($entity);

            return Result::success();
        }

        return Result::fail($entity->getMessages());
    }

    private function isValid(): void
    {
        $modelClass = $this->getModelName();
        if ($modelClass == null or !class_exists($modelClass))
            throw new Exception('Model class not found: ' . $modelClass);

        $primaryKey = $this->getPrimaryKey();
        if (!$primaryKey)
            throw new Exception('Primary key not found on model');

        $primaryFieldValue = $this->primaryKey;
        if ($primaryFieldValue == null)
            throw new Exception('Primary field value is not set');
    }
}