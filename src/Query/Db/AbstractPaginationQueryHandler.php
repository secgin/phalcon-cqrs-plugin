<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\Query\BuilderInterface;
use Phalcon\Paginator\PaginatorFactory;
use Phalcon\Paginator\RepositoryInterface;

/**
 * @property ManagerInterface $modelsManager
 */
abstract class AbstractPaginationQueryHandler implements InjectionAwareInterface
{
    final protected function fetchPagination(BuilderInterface $builder, int $page, int $limit): RepositoryInterface
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

    public function __get($name)
    {
        if ($this->getDI()->has($name))
            return $this->getDI()->get($name);

        return null;
    }

    #region InjectionAwareInterface
    private DiInterface $container;

    public function getDI(): DiInterface
    {
        if (!isset($this->container))
            $this->container = Di::getDefault();

        return $this->container;
    }

    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }
    #endregion
}