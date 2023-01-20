<?php

namespace YG\Phalcon\Cqrs\Command\Db\Handler;

use Exception;
use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Mvc\Model\Transaction\Manager;
use YG\Phalcon\Cqrs\Command\CommandResult;
use YG\Phalcon\Cqrs\Command\CommandResultInterface;

abstract class AbstractDbCommandHandler implements InjectionAwareInterface
{
    private DiInterface $container;

    /**
     * @throws Exception
     */
    protected function transaction($func): CommandResultInterface
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

    public function getDI(): DiInterface
    {
        if (!isset($this->container))
            $this->container = Di::getDefault();

        return $this->container;
    }

    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }

    public function __get($name)
    {
        if ($this->getDI()->has($name))
            return $this->getDI()->get($name);

        return null;
    }
}