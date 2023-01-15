<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Exception;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\QueryBuilder;
use Phalcon\Paginator\RepositoryInterface;

abstract class AbstractFindPaginationDbQuery extends AbstractDbPaginationQuery
{
    use ModelTrait;

    final protected function fetch(): RepositoryInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $modelClass = $this->getModelName();
        if (!class_exists($modelClass))
            throw new Exception('Model not found: ' . $modelClass);

        $modelName = $this->getModelName();

        $builder = $this->modelsManager->createBuilder()
            ->from($modelName);

        $data = $this->getData();
        unset($data['page']);
        unset($data['limit']);
        unset($data['sort']);

        $criteria = Criteria::fromInput($this->getDI(), $modelName, $data);

        if ($criteria->getWhere())
            $builder->andWhere($criteria->getConditions(), $criteria->getParams()['bind']);

        if ($this->sort != '')
            $builder->orderBy($this->getSort());

        $paginator = new QueryBuilder([
            'builder' => $builder,
            'limit' => $this->getLimit(),
            'page' => $this->getPage()
        ]);

        return $paginator->paginate();
    }
}