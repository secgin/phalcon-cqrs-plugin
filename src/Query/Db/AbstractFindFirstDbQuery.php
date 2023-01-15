<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Exception;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\ModelInterface;

abstract class AbstractFindFirstDbQuery extends AbstractDbQuery
{
    use ModelTrait;

    final protected function fetch(): ?ModelInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $modelClass = $this->getModelName();
        if (!class_exists($modelClass))
            throw new Exception('Model not found: ' . $modelClass);

        $modelName = $this->getModelName();
        $data = $this->getData();

        $builder = Criteria::fromInput($this->getDI(), $modelName, $data);

        return $modelName::findFirst([
            'conditions' => $builder->getConditions(),
            'bind' => $data
        ]);
    }
}