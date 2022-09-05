<?php

namespace YG\Phalcon\Query\Db\Handler;

use Phalcon\Di\Injectable;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\Model\ResultsetInterface;
use YG\Phalcon\Query\Db\AbstractFindDbQuery;

final class FindDbQueryHandler extends Injectable
{
    public function handle(AbstractFindDbQuery $query): ResultsetInterface
    {
        $modelClass = $query->modelClass;
        if (empty($modelClass))
            throw new \Error('Model class is not set');

        $builder = Criteria::fromInput($this->getDI(), $modelClass, $query->getData());

        return $builder->execute();
    }
}