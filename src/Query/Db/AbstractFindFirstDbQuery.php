<?php

namespace YG\Phalcon\Query\Db;

use YG\Phalcon\Query\AbstractQuery;

abstract class AbstractFindFirstDbQuery extends AbstractQuery
{
    private string $modelClass;

    protected function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    protected function getModelClass(): string
    {
        return $this->modelClass;
    }
}