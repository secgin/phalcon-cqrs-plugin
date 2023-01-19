<?php

namespace YG\Phalcon\Cqrs\Query\Db;

trait ModelTrait
{
    private string $modelName;

    private ?string $primaryKey;

    protected function setModelName(string $modelName, string $primaryKey = null): void
    {
        $this->modelName = $modelName;
        $this->primaryKey = $primaryKey;
    }

    public function getModelName(): ?string
    {
        return $this->modelName ?? null;
    }

    public function getPrimaryKey(): ?string
    {
        return $this->primaryKey ?? null;
    }
}