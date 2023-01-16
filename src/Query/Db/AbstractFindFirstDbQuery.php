<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Exception;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\ModelInterface;

abstract class AbstractFindFirstDbQuery extends AbstractDbQuery
{
    use ModelTrait;
    use FindDbQueryValidationTrait;

    final protected function fetch(): ?ModelInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $this->isModelNameValid();

        $modelName = $this->getModelName();
        $primaryKey = $this->getPrimaryKey();
        $modelPrimaryKey = $this->getModelPrimaryKey();
        $data = $this->getData();

        echo $primaryKey . '-' . $modelPrimaryKey;

        if ($primaryKey != null and $primaryKey != $modelPrimaryKey)
        {
            if (isset($data[$primaryKey]))
            {
                $data[$modelPrimaryKey] = $data[$primaryKey];
                unset($data[$primaryKey]);
            }
        }

        $builder = Criteria::fromInput($this->getDI(), $modelName, $data);

        return $modelName::findFirst([
            'conditions' => $builder->getConditions(),
            'bind' => $data
        ]);
    }
}