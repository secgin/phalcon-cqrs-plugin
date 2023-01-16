<?php

namespace YG\Phalcon\Cqrs\Command;

interface ManagerInterface
{
    public function notifyEvent(string $eventName, AbstractCommand $command, CommandResultInterface $result): void;
}