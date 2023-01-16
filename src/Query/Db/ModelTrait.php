<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Exception;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * @property ManagerInterface  $modelsManager
 * @property MetaDataInterface $modelsMetadata
 */
trait ModelTrait
{
    private string $modelName;

    private ?string $primaryKey;

    final protected function setModelName(string $modelName, string $primaryKey = null): void
    {
        $this->modelName = $modelName;
        $this->primaryKey = $primaryKey;
    }

    final protected function getModelName(): ?string
    {
        return $this->modelName ?? null;
    }

    final private function getPrimaryKey(): ?string
    {
        return $this->primaryKey ?? null;
    }

    protected function getModel(array $data = []): ModelInterface
    {
        $modelClass = $this->getModelName();
        if (!class_exists($modelClass))
            throw new Exception('Not found model on the ' . $modelClass . ' command');

        $model = new $modelClass();
        $model->assign($data);
        return $model;
    }

    final protected function getModelPrimaryKey(): ?string
    {
        $primaryKeys = $this->modelsMetadata->getPrimaryKeyAttributes($this->getModel());
        return $primaryKeys[0] ?? null;
    }
}