<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Mvc\Model\Criteria;
use Phalcon\Mvc\ModelInterface;
use YG\Phalcon\Cqrs\Query\AbstractQuery;

/**
 * @method static ModelInterface fetch(array $data = [])
 */
abstract class AbstractFindFirstQuery extends AbstractQuery
{
    use ModelTrait;

    final protected function handle(): ModelInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $modelName = $this->getModelName();
        $data = $this->getQueryData();

        $builder = Criteria::fromInput($this->getDI(), $modelName, $data);

        return $modelName::findFirst([
            'conditions' => $builder->getConditions(),
            'bind' => $data
        ]);
    }

    private function getQueryData(): array
    {
        $primaryKey = $this->getPrimaryKey();
        $modelPrimaryKey = $this->getModelPrimaryKey($this->getModelName());

        $data = $this->getData();
        if ($primaryKey != null and $primaryKey != $modelPrimaryKey)
        {
            if (isset($data[$primaryKey]))
            {
                $data[$modelPrimaryKey] = $data[$primaryKey];
                unset($data[$primaryKey]);
            }
        }

        return $data;
    }

    private function getModelPrimaryKey(string $modelName): ?string
    {
        $model = new $modelName;
        $primaryKeys = $this->modelsMetadata->getPrimaryKeyAttributes($model);
        return $primaryKeys[0] ?? null;
    }
}