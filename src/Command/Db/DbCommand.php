<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Error;
use Exception;
use Phalcon\Mvc\Model\Transaction\Manager;
use Phalcon\Mvc\Model\TransactionInterface;
use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\CommandResultInterface;

abstract class DbCommand extends \YG\Phalcon\Cqrs\Command\Command
{
    private TransactionInterface $transaction;

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

    protected function getTransaction(): TransactionInterface
    {
        if (!isset($this->transaction))
        {
            $txManager = new Manager();
            $this->transaction = $txManager->get();
        }

        return $this->transaction;
    }

    /**
     * @throws Exception
     */
    final protected function internalHandle(): CommandResultInterface
    {
        try
        {
            $result = $this->handle();

            if (isset($this->transaction))
            {
                if ($result->isSuccess())
                    $this->transaction->commit();
                else
                    $this->transaction->rollback();
            }

            return $result;
        }
        catch (Exception|Error $exception)
        {
            if (isset($this->transaction))
                $this->transaction->rollback();

            throw $exception;
        }
    }
}