<?php

namespace YG\Phalcon\Cqrs\Query;

interface QueryDispatcherInterface
{
    public function dispatch(AbstractQuery $query);
}