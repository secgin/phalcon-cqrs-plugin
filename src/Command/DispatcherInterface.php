<?php

namespace YG\Phalcon\Cqrs\Command;

interface DispatcherInterface
{
    public function dispatch(Command $command): CommandResultInterface;

    public function notifyEvent(string $eventName, Command $command, $result): void;
}