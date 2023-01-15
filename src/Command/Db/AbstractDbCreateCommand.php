<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use YG\Phalcon\Cqrs\Command\Result;
use YG\Phalcon\Cqrs\Command\ResultInterface;

abstract class AbstractDbCreateCommand extends AbstractDbCommand
{
    use ModelTrait;

    final protected function execute(): ResultInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $entity = $this->getModel($this->getData());

        if (method_exists($this, 'beforeExecute'))
            $this->beforeExecute($entity);

        if ($entity->create())
        {
            if (method_exists($this, 'afterExecute'))
                $this->afterExecute($entity);

            return Result::success($entity->id);
        }
        return Result::fail($entity->getMessages());
    }
}