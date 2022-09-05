<?php

namespace YG\Phalcon\Command;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class CommandDispatcherProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared('commandDispatcher', CommandDispatcher::class);
    }
}