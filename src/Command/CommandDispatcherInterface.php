<?php

namespace YG\Phalcon\Cqrs\Command;

interface CommandDispatcherInterface
{
    public function dispatch(AbstractCommand $command): CommandResult;
}