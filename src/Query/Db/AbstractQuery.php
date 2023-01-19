<?php

namespace YG\Phalcon\Cqrs\Query\Db;

abstract class AbstractQuery extends \YG\Phalcon\Cqrs\Query\AbstractQuery
{
    use PaginationTrait;
}