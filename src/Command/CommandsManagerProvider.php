<?php

namespace YG\Phalcon\Cqrs\Command;

use Phalcon\Di\DiInterface;
use Phalcon\Di\ServiceProviderInterface;

class CommandsManagerProvider implements ServiceProviderInterface
{
    public function register(DiInterface $di): void
    {
        $di->setShared('commandsManager', Manager::class);
    }
}