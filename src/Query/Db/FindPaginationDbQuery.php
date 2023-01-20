<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Error;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Paginator\RepositoryInterface;

/**
 * @method static RepositoryInterface fetch(array $data = [])
 */
abstract class FindPaginationDbQuery extends PaginationDbQuery
{
    use PaginationTrait;

    abstract protected function getBuilder(): BuilderInterface;

    final protected function handle(): RepositoryInterface
    {
        $builder = $this->getBuilder();

        $this->addCondition($builder);

        if ($this->getSort() != '')
            $builder->orderBy($this->getSort());

        return $this->fetchPagination($builder, $this->getPage(), $this->getLimit());
    }

    protected function addCondition(BuilderInterface $builder): void
    {
        $from = $builder->getFrom();
        if (is_string($from))
            $modelName = $from;
        elseif (is_array($from))
            $modelName = $from[array_key_first($from)];
        else
            throw new Error('Model name not be set (' . get_called_class() . ')');

        $data = $this->getData();
        unset($data['page']);
        unset($data['limit']);
        unset($data['sort']);

        $criteria = Criteria::fromInput($this->getDI(), $modelName, $data);

        if ($criteria->getWhere())
            $builder->andWhere($criteria->getConditions(), $criteria->getParams()['bind']);
    }
}