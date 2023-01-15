<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Phalcon\Exception;
use Phalcon\Mvc\ModelInterface;

trait ModelTrait
{
    private string $modelName;

    private string $primaryKey;

    final protected function setModelName(string $modelName, string $primaryKey = 'id'): void
    {
        $this->modelName = $modelName;
        $this->primaryKey = $primaryKey;
    }

    final protected function getModelName(): ?string
    {
        return $this->modelName ?? null;
    }

    final protected function getPrimaryKey(): ?string
    {
        if (!isset($this->primaryKey))
        {
            $primaryKeys = $this->modelsMetadata->getPrimaryKeyAttributes($this->getModel());
            $this->primaryKey = $primaryKeys[0] ?? null;
        }
        return $this->primaryKey ?? null;
    }

    protected function getModel(array $data = []): ModelInterface
    {
        $modelClass = $this->getModelName();
        if (!class_exists($modelClass))
            throw new Exception('Model not found: ' . $modelClass);

        $model = new $modelClass();
        $model->assign($data);
        return $model;
    }
}