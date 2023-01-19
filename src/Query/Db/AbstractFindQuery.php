<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\ResultsetInterface;
use YG\Phalcon\Cqrs\Query\AbstractQuery;

/**
 * @method static ResultsetInterface fetch(array $data = [])
 */
abstract class AbstractFindQuery extends AbstractQuery
{
    use ModelTrait;

    final protected function handle(): ResultsetInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        return Criteria::fromInput($this->getDI(), $this->getModelName(), $this->getData())
            ->execute();
    }
}