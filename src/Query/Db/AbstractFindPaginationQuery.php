<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\RepositoryInterface;
use YG\Phalcon\Cqrs\Query\AbstractPaginationQuery;

/**
 * @method static RepositoryInterface fetch(array $data = [])
 */
abstract class AbstractFindPaginationQuery extends AbstractPaginationQuery
{
    use ModelTrait;

    use PaginationTrait;

    final protected function handle(): RepositoryInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $modelName = $this->getModelName();

        $builder = $this->createBuilder()->from($modelName);

        $data = $this->getData();
        unset($data['page']);
        unset($data['limit']);
        unset($data['sort']);

        $criteria = Criteria::fromInput($this->getDI(), $modelName, $data);

        if ($criteria->getWhere())
            $builder->andWhere($criteria->getConditions(), $criteria->getParams()['bind']);

        if ($this->getSort() != '')
            $builder->orderBy($this->getSort());

        return $this->fetchPagination($builder, $this->getPage(), $this->getLimit());
    }
}