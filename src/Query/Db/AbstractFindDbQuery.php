<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Exception;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\ResultsetInterface;

abstract class AbstractFindDbQuery extends AbstractDbQuery
{
    use ModelTrait;

    final protected function fetch(): ResultsetInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $modelClass = $this->getModelName();
        if (!class_exists($modelClass))
            throw new Exception('Model not found: ' . $modelClass);

        return Criteria::fromInput($this->getDI(), $this->getModelName(), $this->getData())
            ->execute();
    }
}