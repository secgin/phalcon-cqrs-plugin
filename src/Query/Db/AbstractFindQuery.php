<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Error;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Mvc\Model\ResultsetInterface;
use YG\Phalcon\Cqrs\Query\AbstractQuery;

/**
 * @method static ResultsetInterface fetch(array $data = [])
 */
abstract class AbstractFindQuery extends AbstractQuery
{
    use PaginationTrait;

    final protected function handle(): ResultsetInterface
    {
        $builder = $this->getBuilder();
        $this->addCondition($builder);
        return $builder->getQuery()->execute();
    }

    abstract protected function getBuilder(): BuilderInterface;

    protected function addCondition(BuilderInterface $builder): void
    {
        $from = $builder->getFrom();
        if (is_string($from))
            $modelName = $from;
        elseif (is_array($from))
            $modelName = $from[array_key_first($from)];
        else
            throw new Error('Model name not be set (' . get_called_class() . ')');

        $criteria = Criteria::fromInput($this->getDI(), $modelName, $this->getData());

        if ($criteria->getWhere())
            $builder->andWhere($criteria->getConditions(), $criteria->getParams()['bind']);
    }
}