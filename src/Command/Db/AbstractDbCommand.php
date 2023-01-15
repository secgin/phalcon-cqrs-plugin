<?php

namespace YG\Phalcon\Cqrs\Command\Db;

use Phalcon\Exception;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\Model\Transaction\Manager;
use YG\Phalcon\Cqrs\Command\AbstractCommand;
use YG\Phalcon\Cqrs\Command\Result;
use YG\Phalcon\Cqrs\Command\ResultInterface;

/**
 * @property ManagerInterface  $modelsManager
 * @property MetaDataInterface $modelsMetadata
 */
abstract class AbstractDbCommand extends AbstractCommand
{
    abstract protected function execute(): ResultInterface;

    /**
     * @throws Exception
     */
    protected function transaction(callable $callable): ResultInterface
    {
        try
        {
            $txManager = new Manager();
            $transaction = $txManager->get();

            $result = $callable($transaction);
            if (!$result or $result->isFail())
            {
                $transaction->rollback();
                return $result;
            }

            $transaction->commit();
            return Result::success();
        }
        catch (Exception $exception)
        {
            if (isset($transaction) and $transaction != null)
                $transaction->rollback();

            throw $exception;
        }
    }
}