<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\CommandResultInterface;

abstract class AbstractDbCreateCommand extends AbstractDbCommand
{
    use ModelTrait;

    final protected function execute(): CommandResultInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $entity = $this->getModel();

        if (method_exists($this, 'beforeExecute'))
            $this->beforeExecute($entity);

        if ($entity->create())
        {
            if (method_exists($this, 'afterExecute'))
                $this->afterExecute($entity);

            return CommandResult::success($entity->id);
        }
        return CommandResult::fail($entity->getMessages());
    }
}