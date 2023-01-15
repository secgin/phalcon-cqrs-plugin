<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Phalcon\Exception;
use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\CommandResultInterface;

abstract class AbstractDbUpdateCommand extends AbstractDbCommand
{
    use ModelTrait;

    final protected function execute(): CommandResultInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $this->isValid();

        $modelClass = $this->getModelName();
        $primaryKey = $this->getPrimaryKey();
        $primaryKeyValue = $this->$primaryKey;

        $entity = $modelClass::findFirst($primaryKeyValue);
        if (!$entity)
            return CommandResult::fail('Entity not found');


        $data = $this->getData();
        unset($data[$primaryKey]);
        $entity->assign($data);

        if (method_exists($this, 'beforeExecute'))
            $this->beforeExecute($entity);

        if ($entity->update())
        {
            if (method_exists($this, 'afterExecute'))
                $this->afterExecute($entity);

            return CommandResult::success();
        }

        return CommandResult::fail($entity->getMessages());
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