<?php

namespace YG\Phalcon\Query\Db\Handler;

use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Paginator\RepositoryInterface;
use YG\Phalcon\Query\Db\AbstractFindPaginationDbQuery;

class FindPaginationDbQueryHandler extends Injectable
{
    public function handle(AbstractFindPaginationDbQuery $query): RepositoryInterface
    {
        $modelClass = $query->modelClass;
        if (empty($modelClass))
            throw new \Error('Model class is not set');

        $builder = $this->modelsManager->createBuilder()
            ->from($modelClass);

        $criteria = Criteria::fromInput($this->getDI(), $modelClass, $query->getData());

        if ($criteria->getWhere())
            $builder->andWhere($criteria->getConditions(), $criteria->getParams()['bind']);

        if ($query->sort != '')
            $builder->orderBy($query->sort);

        $paginator = new QueryBuilder([
            'builder' => $builder,
            'limit' => $query->limit,
            'page' => $query->page
        ]);

        return $paginator->paginate();
    }
}