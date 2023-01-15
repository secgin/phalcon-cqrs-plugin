<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Exception;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * @property ManagerInterface $modelsManager
 */
trait ModelTrait
{
    private string $modelName;

    final protected function setModelName(string $modelName): void
    {
        $this->modelName = $modelName;
    }

    final protected function getModelName(): ?string
    {
        return $this->modelName ?? null;
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