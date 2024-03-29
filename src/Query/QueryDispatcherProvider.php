<?php

namespace YG\Phalcon\Cqrs\Query;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class QueryDispatcherProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared('queryDispatcher', Dispatcher::class);
    }
}