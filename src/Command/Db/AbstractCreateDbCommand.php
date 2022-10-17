<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use YG\Phalcon\Cqrs\Command\AbstractCommand;

/**
 * @property string $modelClass
 */
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