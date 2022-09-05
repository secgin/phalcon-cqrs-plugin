<?php

namespace YG\Phalcon\Command\Db\Handler;

use Error;
use YG\Phalcon\Command\CommandResult;
use YG\Phalcon\Command\Db\AbstractDeleteDbCommand;

final class DeleteDbCommandHandler
{
    public function handle(AbstractDeleteDbCommand $command): CommandResult
    {
        $modelClass = $command->modelClass;
        if (!class_exists($modelClass))
            throw new Error('Model not found: ' . $modelClass);

        $primaryField = $command->primaryField;
        if ($primaryField == '')
            throw new Error('Primary field not found');

        $data = $command->getData();
        $primaryFieldValue = $data[$primaryField] ?? null;
        if ($primaryFieldValue == null)
            throw new Error('Primary field value is not set');
        unset($data[$primaryField]);

        $entity = $modelClass::findFirst($primaryFieldValue);
        if (!$entity)
            return CommandResult::fail('Entity not found');

        $entity->assign($data);

        if ($entity->delete())
            return CommandResult::success($primaryFieldValue);

        return CommandResult::fail($entity);
    }
}