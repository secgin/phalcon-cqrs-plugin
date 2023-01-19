<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Paginator\PaginatorFactory;
use Phalcon\Paginator\RepositoryInterface;

trait PaginationTrait
{
    final protected function createBuilder(): BuilderInterface
    {
        return $this->getModelsManager()->createBuilder();
    }

    final protected function fetchPagination(BuilderInterface $builder, int $page, int $limit): RepositoryInterface
    {
        $paginate = $this->execute($builder, $page, $limit);

        $pageCounts = ceil($paginate->getTotalItems() / $limit);
        if ($page > $pageCounts)
            $paginate = $this->execute($builder, $pageCounts, $limit);

        return $paginate;
    }

    private function execute(BuilderInterface $builder, int $page, int $limit): RepositoryInterface
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

    private function getModelsManager(): ManagerInterface
    {
        return $this->getDI()->get('modelsManager');
    }
}