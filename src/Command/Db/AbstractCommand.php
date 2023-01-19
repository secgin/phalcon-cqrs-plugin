<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Exception;
use Phalcon\Mvc\Model\Transaction\Manager;
use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\CommandResultInterface;

abstract class AbstractCommand extends \YG\Phalcon\Cqrs\Command\AbstractCommand
{
    final protected function transaction($func): CommandResultInterface
    {
        try
        {
            $txManager = new Manager();
            $transaction = $txManager->get();

            $result = $func($transaction);
            if (!$result or $result->isFail())
            {
                $transaction->rollback();
                return $result;
            }

            $transaction->commit();
            return CommandResult::success();
        }
        catch (Exception $exception)
        {
            if (isset($transaction) and $transaction != null)
                $transaction->rollback();

            throw $exception;
        }
    }

    abstract protected function handle(): CommandResultInterface;
}