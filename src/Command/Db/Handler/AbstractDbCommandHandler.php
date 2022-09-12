<?php

namespace YG\Phalcon\Cqrs\Command\Db\Handler;

use Exception;
use Phalcon\Mvc\Model\Transaction\Manager;
use YG\Phalcon\Cqrs\Command\CommandResult;

abstract class AbstractDbCommandHandler
{
    /**
     * @throws Exception
     */
    protected function transaction($func): CommandResult
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
}