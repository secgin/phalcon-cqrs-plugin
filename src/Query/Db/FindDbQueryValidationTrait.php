<?php

namespace YG\Phalcon\Cqrs\Query\Db;

use Phalcon\Exception;
use YG\Phalcon\Cqrs\NullOrEmptyFieldException;

trait FindDbQueryValidationTrait
{
    protected function isModelNameValid(): void
    {
        $modelClass = $this->getModelName();

        if (empty($modelClass))
            throw new NullOrEmptyFieldException('Model name not assigned for ' . get_called_class() . ' query');

        if (!class_exists($modelClass))
            throw new Exception('Not found model on the '. $modelClass. ' query');
    }
}