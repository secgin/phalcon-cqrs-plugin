<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use YG\Phalcon\Cqrs\Query\AbstractPaginationQuery;

abstract class AbstractFindPaginationDbQuery extends AbstractPaginationQuery
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