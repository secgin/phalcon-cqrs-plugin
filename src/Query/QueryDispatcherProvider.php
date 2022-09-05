<?php

namespace YG\Phalcon\Cqrs\Query;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class QueryDispatcherProvider implements ServiceProviderInterface
{
    static public string $serviceName = 'queryDispatcher';

    public function register(DiInterface $di): void
    {
        $di->setShared(self::$serviceName, QueryDispatcher::class);
    }
}