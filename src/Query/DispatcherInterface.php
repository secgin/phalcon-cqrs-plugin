<?php

namespace YG\Phalcon\Cqrs\Query;

interface DispatcherInterface
{
    /**
     * @param AbstractQuery $query
     *
     * @return mixed
     */
    public function dispatch(AbstractQuery $query);

    public function notifyEvent(string $eventName, AbstractQuery $query, $result = null): void;
}