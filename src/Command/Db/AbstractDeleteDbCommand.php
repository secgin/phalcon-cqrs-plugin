<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use YG\Phalcon\Cqrs\Command\AbstractCommand;

/**
 * @property string $modelClass
 * @property string $primaryField
 */
abstract class AbstractDeleteDbCommand extends AbstractCommand
{
    private string
        $modelClass,
        $primaryField;

    protected function setModelClass(string $modelClass): void
    {
        $this->modelClass = $modelClass;
    }

    protected function getModelClass(): string
    {
        return $this->modelClass;
    }

    protected function setPrimaryField(string $primaryField): void
    {
        $this->primaryField = $primaryField;
    }

    protected function getPrimaryField(): string
    {
        return $this->primaryField;
    }
}