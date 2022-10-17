<?php

namespace YG\Phalcon\Cqrs\Query;

use Error;
use Phalcon\Di\Injectable;
use YG\Phalcon\Cqrs\Query\Db\AbstractFindDbQuery;
use YG\Phalcon\Cqrs\Query\Db\AbstractFindFirstDbQuery;
use YG\Phalcon\Cqrs\Query\Db\AbstractFindPaginationDbQuery;
use YG\Phalcon\Cqrs\Query\Db\Handler\FindDbQueryHandler;
use YG\Phalcon\Cqrs\Query\Db\Handler\FindFirstDbQueryHandler;
use YG\Phalcon\Cqrs\Query\Db\Handler\FindPaginationDbQueryHandler;

final class QueryDispatcher extends Injectable implements QueryDispatcherInterface
{
    private array $handlers = [];

    public function dispatch(AbstractQuery $query)
    {
        $queryHandler = $this->getQueryHandler($query);

        if ($queryHandler == null)
            throw new Error('Not Found Query Handler');

        if (!method_exists($queryHandler, 'handle'))
            throw new Error('Not Found Query Handler Method');

        return $queryHandler->handle($query);
    }

    private function getQueryHandler(AbstractQuery $query)
    {
        $queryClass = get_class($query);

        if (array_key_exists($queryClass, $this->handlers))
            return $this->handlers[$queryClass];

        $queryHandlerClassName = str_replace('Queries\\', 'QueryHandlers\\', $queryClass) . "QueryHandler";
        if (class_exists($queryHandlerClassName))
            return new $queryHandlerClassName;

        if (is_subclass_of($query, AbstractFindDbQuery::class))
            return new FindDbQueryHandler();
        elseif (is_subclass_of($query, AbstractFindFirstDbQuery::class))
            return new FindFirstDbQueryHandler();
        elseif (is_subclass_of($query, AbstractFindPaginationDbQuery::class))
            return new FindPaginationDbQueryHandler();

        return null;
    }
}