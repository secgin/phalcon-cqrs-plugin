<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Error;
use YG\Phalcon\Cqrs\Command\CommandResult;

abstract class AbstractCreateDbCommand extends AbstractDbCommand
{
    private string $modelClass;

    protected function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    final public function execute(): CommandResult
    {
        $modelClass = $this->modelClass;
        if (!class_exists($modelClass))
            throw new Error('Model not found: ' . $modelClass);

        $entity = new $modelClass();
        $entity->assign($this->getData());

        $this->beforeExecute($entity);

        if ($entity->create())
        {
            $this->afterExecute($entity);
            return CommandResult::success($entity->id);
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