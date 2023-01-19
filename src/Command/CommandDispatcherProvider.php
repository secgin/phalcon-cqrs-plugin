<?php

namespace YG\Phalcon\Cqrs\Command;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class CommandDispatcherProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared('commandDispatcher', Dispatcher::class);
    }
}