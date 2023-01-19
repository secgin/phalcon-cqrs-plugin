<?php

namespace YG\Phalcon\Cqrs\Query\Db;

abstract class AbstractPaginationQuery extends \YG\Phalcon\Cqrs\Query\AbstractPaginationQuery
{
    use PaginationTrait;
}