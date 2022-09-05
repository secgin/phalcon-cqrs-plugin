<?php

namespace YG\Phalcon\Cqrs\Command;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class CommandDispatcherProvider implements ServiceProviderInterface
{
    static public string $serviceName = 'commandDispatcher';

    public function register(DiInterface $di): void
    {
        $di->setShared(self::$serviceName, CommandDispatcher::class);
    }
}