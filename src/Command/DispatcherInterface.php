<?php

namespace YG\Phalcon\Cqrs\Command;

interface DispatcherInterface
{
    public function dispatch(AbstractCommand $command): CommandResultInterface;

    public function notifyEvent(string $eventName, AbstractCommand $command, $result): void;
}