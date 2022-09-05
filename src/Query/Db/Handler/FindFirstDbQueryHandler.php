<?php

namespace YG\Phalcon\Query\Db\Handler;

use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\ModelInterface;
use YG\Phalcon\Query\Db\AbstractFindFirstDbQuery;

final class FindFirstDbQueryHandler extends Injectable
{
    public function handle(AbstractFindFirstDbQuery $query): ?ModelInterface
    {
        $modelClass = $query->modelClass;
        if (empty($modelClass))
            throw new \Error('Model class is not set');

        $builder = Criteria::fromInput($this->getDI(), $modelClass, $query->getData());

        return $modelClass::findFirst([
            'conditions' => $builder->getConditions(),
            'bind' => $query->getData()
        ]);
    }
}