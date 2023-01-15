<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use YG\Phalcon\Cqrs\Query\AbstractQuery;

abstract class AbstractDbQuery extends AbstractQuery
{
    public function __construct()
    {
        parent::__construct();
    }

    abstract protected function fetch();
}