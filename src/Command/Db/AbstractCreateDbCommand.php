<?php

namespace YG\Phalcon\Command\Db;

use YG\Phalcon\Command\AbstractCommand;

abstract class AbstractCreateDbCommand extends AbstractCommand
{
    private string $modelClass;

    protected function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    protected function getModelClass(): string
    {
        return $this->modelClass;
    }
}