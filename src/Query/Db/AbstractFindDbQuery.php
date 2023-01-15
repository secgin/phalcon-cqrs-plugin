<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Exception;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\ResultsetInterface;

abstract class AbstractFindDbQuery extends AbstractDbQuery
{
    use ModelTrait;
    use FindDbQueryValidationTrait;

    final protected function fetch(): ResultsetInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $this->isModelNameValid();

        return Criteria::fromInput($this->getDI(), $this->getModelName(), $this->getData())
            ->execute();
    }
}