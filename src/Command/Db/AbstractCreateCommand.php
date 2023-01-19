<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Error;
use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\CommandResultInterface;

abstract class AbstractCreateCommand extends AbstractCommand
{
    use ModelTrait;

    final protected function handle(): CommandResultInterface
    {
        if (method_exists($this, 'initialize'))
            $this->initialize();

        $modelName = $this->getModelName();
        if (!class_exists($modelName))
            throw new Error('Model not found: ' . $modelName .' on the ' . get_called_class());

        $model = new $modelName();

        $dataWithBuiltinType = [];
        foreach($this->getData() as $name => $value)
            if (!is_object($value) and !is_array($value))
                $dataWithBuiltinType[$name] = $value;

        $model->assign($dataWithBuiltinType);

        if (method_exists($this, 'beforeCreate'))
            $this->beforeCreate($model);

        if ($model->create())
        {
            if (method_exists($this, 'afterCreate'))
                $this->afterCreate($model);

            return CommandResult::success($model->{$this->getPrimaryKey()});
        }

        return CommandResult::fail($model->getMessages());
    }
}