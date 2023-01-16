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

    final protected function getPrimaryKeyValue()
    {
        return $this->{$this->getPrimaryKey()};
    }

    final protected function getModel(): ModelInterface
    {
        $modelClass = $this->getModelName();
        if (!class_exists($modelClass))
            throw new Exception('Model not found: ' . $modelClass .' on the ' . get_called_class());

        $model = new $modelClass();

        $dataWithBuiltinType = [];
        foreach($this->getData() as $name => $value)
            if (!is_object($value) and !is_array($value))
                $dataWithBuiltinType[$name] = $value;

        $model->assign($dataWithBuiltinType);
        return $model;
    }

    final protected function findFirst(): ?ModelInterface
    {
        /**
         * @var ModelInterface $entity
         */
        return $this->getModelName()::findFirst($this->getPrimaryKeyValue());
    }

    final protected function getDataForModel(): array
    {
        $data = $this->getData();
        unset($data[$this->getPrimaryKey()]);

        $dataWithBuiltinType = [];
        foreach($data as $name => $value)
            if (!is_object($value) and !is_array($value))
                $dataWithBuiltinType[$name] = $value;

        return $dataWithBuiltinType;
    }
}