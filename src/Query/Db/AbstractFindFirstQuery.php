<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Mvc\ModelInterface;
use YG\Phalcon\Cqrs\Query\AbstractQuery;

/**
 * @method static ModelInterface fetch(array $data = [])
 */
abstract class AbstractFindFirstQuery extends AbstractQuery
{
    abstract protected function handle(): ?ModelInterface;
}