<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use YG\Phalcon\Cqrs\Query\AbstractQuery;

abstract class AbstractDbQuery extends AbstractQuery
{
    abstract protected function fetch();
}