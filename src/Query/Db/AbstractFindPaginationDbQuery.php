<?php

namespace YG\Phalcon\Query\Db;

use YG\Phalcon\Query\AbstractPaginationQuery;

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