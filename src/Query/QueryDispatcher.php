<?php

namespace YG\Phalcon\Cqrs\Query;

use Error;
use Phalcon\Annotations\Collection;
use Phalcon\Di\Injectable;
use YG\Phalcon\Cqrs\Query\Db\AbstractFindDbQuery;
use YG\Phalcon\Cqrs\Query\Db\AbstractFindFirstDbQuery;
use YG\Phalcon\Cqrs\Query\Db\AbstractFindPaginationDbQuery;
use YG\Phalcon\Cqrs\Query\Db\Handler\FindDbQueryHandler;
use YG\Phalcon\Cqrs\Query\Db\Handler\FindFirstDbQueryHandler;
use YG\Phalcon\Cqrs\Query\Db\Handler\FindPaginationDbQueryHandler;

final class QueryDispatcher extends Injectable implements QueryDispatcherInterface
{
    private array
        $handlers = [],
        $handlerClasses = [];

    private ?string $queryHandlerNamespace = null;


    public function dispatch(AbstractQuery $query)
    {
        $queryHandler = $this->getQueryHandler($query);

        if ($queryHandler == null)
            throw new Error('Not Found Query Handler');

        if (!method_exists($queryHandler, 'handle'))
            throw new Error('Not Found Query Handler Method');

        return $queryHandler->handle($query);
    }

    public function register(string $queryClass, string $queryHandlerClass): void
    {
        $this->handlerClasses[$queryClass] = $queryHandlerClass;
    }

    public function registerFromArray(array $handlers): void
    {
        $this->handlerClasses = array_merge($this->handlerClasses, $handlers);
    }

    /**
     * Sorgu işleyicisinin otomatik yüklenmesi için gerekli namespace eki.
     * Sorgu işleyicisi register metodotları ile kayıt edilirse gerek duyulmaz.
     */
    public function setNamespace(string $queryHandlerNamespace)
    {
        $this->queryHandlerNamespace = $queryHandlerNamespace;
    }

    private function getQueryHandler(AbstractQuery $query)
    {
        $queryClass = get_class($query);

        if (array_key_exists($queryClass, $this->handlers))
            return $this->handlers[$queryClass];

        if (array_key_exists($queryClass, $this->handlerClasses))
        {
            $queryHandlerClass = $this->handlerClasses[$queryClass];
            $this->handlers[$queryClass] = new $queryHandlerClass;
            return $this->handlers[$queryClass];
        }

        $annotations = $this->annotations->get($queryClass);
        $classAnnotations = $annotations->getClassAnnotations();
        if ($classAnnotations instanceof Collection and $classAnnotations->has('Handler'))
        {
            $queryHandlerClass = $classAnnotations->get('Handler')->getArgument(0);

            if (class_exists($queryHandlerClass))
            {
                $this->handlers[$queryClass] = new $queryHandlerClass;
                return $this->handlers[$queryClass];
            }
        }

        if ($this->queryHandlerNamespace != null)
        {
            $reflection = new \ReflectionClass($query);
            $queryClassShortName = $reflection->getShortName();
            $queryHandlerClass = $this->queryHandlerNamespace . '\\' . $queryClassShortName . 'QueryHandler';

            if (class_exists($queryHandlerClass))
                return new $queryHandlerClass;
        }

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