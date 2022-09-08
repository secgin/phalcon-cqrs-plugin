<?php

namespace YG\Phalcon\Cqrs\Command\Db\Handler;

use Error;
use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\Db\AbstractCreateDbCommand;

final class CreateDbCommandHandler
{
    public function handle(AbstractCreateDbCommand $command): CommandResult
    {
        $modelClass = $command->modelClass;
        if (!class_exists($modelClass))
            throw new Error('Model not found: ' . $modelClass);

        $model = new $modelClass();
        $model->assign($command->getData());
        if ($model->create())
            return CommandResult::success($model->id);

        $messages = [];
        foreach ($model->getMessages() as $message)
        {
            if (is_array($message->getField()))
                $messages[] = $message->getMessage();
            else
                $messages[$message->getField()] = $message->getMessage();
        }

        return CommandResult::fail($messages);
    }
}