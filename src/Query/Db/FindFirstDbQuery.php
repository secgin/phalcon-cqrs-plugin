<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Mvc\ModelInterface;

/**
 * @method static ModelInterface fetch(array $data = [])
 */
abstract class FindFirstDbQuery extends DbQuery
{
    abstract protected function handle(): ?ModelInterface;
}