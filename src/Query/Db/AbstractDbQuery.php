<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Paginator\PaginatorFactory;
use Phalcon\Paginator\RepositoryInterface;
use YG\Phalcon\Cqrs\Query\AbstractQuery;

abstract class AbstractDbQuery extends AbstractQuery
{
    abstract protected function fetch();

    final protected function fetchPagination($builder, int $page, int $limit): RepositoryInterface
    {
        $paginator = (new PaginatorFactory())->newInstance('queryBuilder',
            [
                'builder' => $builder,
                'limit' => $limit,
                'page' => $page,
                'repository' => new PaginationRepository()
            ]
        );

        return $paginator->paginate();
    }
}