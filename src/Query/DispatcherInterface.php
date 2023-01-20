<?php

namespace YG\Phalcon\Cqrs\Query;

interface DispatcherInterface
{
    /**
     * @param Query $query
     *
     * @return mixed
     */
    public function dispatch(Query $query);

    public function notifyEvent(string $eventName, Query $query, $result = null): void;
}