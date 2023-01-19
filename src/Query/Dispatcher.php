<?php

namespace YG\Phalcon\Cqrs\Query;

use Error;
use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Exception;
use Phalcon\Events\ManagerInterface as EventsManagerInterface;
use Throwable;

class Dispatcher extends Injectable implements DispatcherInterface, EventsAwareInterface
{
    private array $handlers = [];

    public function dispatch(AbstractQuery $query)
    {
        if (!$this->getDI()->has(get_class($query)))
        {
            if (method_exists($query, 'handle'))
                return $query->fetch();
        }

        try
        {
            $queryHandler = $this->getQueryHandler($query);

            if ($queryHandler == null)
                throw new Error('Not Found Query Handler');

            if (!method_exists($queryHandler, 'handle'))
                throw new Error('Not Found Query Handler Method');

            $this->notifyEvent('beforeFetch', $query);

            $result = $queryHandler->handle($query);

            $this->notifyEvent('afterFetch', $query, $result);

            return $result;
        }
        catch (Exception|Error|Throwable $ex)
        {
            $this->notifyEvent('error', $query, $ex);
            return null;
        }
    }

    /**
     * @param AbstractQuery $query
     *
     * @return mixed|AbstractQuery|null
     */
    private function getQueryHandler(AbstractQuery $query)
    {
        $queryClass = get_class($query);

        if (array_key_exists($queryClass, $this->handlers))
            return $this->handlers[$queryClass];

        if ($this->getDI()->has($queryClass))
        {
            $queryHandler = $this->getDI()->get($queryClass);
            $this->handlers[$queryClass] = $queryHandler;
            return $queryHandler;
        }

        $queryHandlerClassName = $this->getQueryHandlerClassName($query);
        if (class_exists($queryHandlerClassName))
        {
            $queryHandler = new $queryHandlerClassName;
            $this->handlers[$queryClass] = $queryHandler;
            return $queryHandler;
        }

        return null;
    }

    private function getQueryHandlerClassName(AbstractQuery $query): ?string
    {
        $queryClass = get_class($query);

        $arr = explode('\\', $queryClass);
        $queryName = array_pop($arr);

        array_pop($arr);
        $namespace = join('\\', $arr);

        $queryHandlerClassName = $namespace . '\\QueryHandlers\\' . $queryName . "QueryHandler";
        if (class_exists($queryHandlerClassName))
            return $queryHandlerClassName;

        $queryHandlerClassName = $namespace . '\\Services\\QueryHandlers\\' . $queryName . "QueryHandler";
        if (class_exists($queryHandlerClassName))
            return $queryHandlerClassName;

        array_pop($arr);
        $namespace = join('\\', $arr);

        $queryHandlerClassName = $namespace . '\\QueryHandlers\\' . $queryName . "QueryHandler";
        if (class_exists($queryHandlerClassName))
            return $queryHandlerClassName;

        $queryHandlerClassName = $namespace . '\\Services\\QueryHandlers\\' . $queryName . "QueryHandler";
        if (class_exists($queryHandlerClassName))
            return $queryHandlerClassName;

        return null;
    }

    public function notifyEvent(string $eventName, AbstractQuery $query, $result = null): void
    {
        if (isset($this->eventsManager))
            $this->eventsManager->fire('queryDispatcher:' . $eventName, $query, $result);
    }

    #region EventsAwareInterface
    private EventsManagerInterface $eventsManager;

    public function getEventsManager(): ?EventsManagerInterface
    {
        return $this->eventsManager ?? null;
    }

    public function setEventsManager(EventsManagerInterface $eventsManager): void
    {
        $this->eventsManager = $eventsManager;
    }
    #endregion
}