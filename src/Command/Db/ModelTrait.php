<?php

namespace YG\Phalcon\Cqrs\Command\Db;

trait ModelTrait
{
    private string $modelName;

    private string $primaryKey;

    final protected function setModelName(string $modelName, string $primaryKey = 'id'): void
    {
        $this->modelName = $modelName;
        $this->primaryKey = $primaryKey;
    }

    final public function getModelName(): ?string
    {
        return $this->modelName ?? null;
    }

    final public function getPrimaryKey(): ?string
    {
        return $this->primaryKey ?? null;
    }
}