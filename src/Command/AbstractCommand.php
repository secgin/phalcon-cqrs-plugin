<?php

namespace YG\Phalcon\Cqrs\Command;

use Error;
use Phalcon\Exception;
use YG\Phalcon\Cqrs\AbstractRequest;
use YG\Phalcon\Cqrs\Command\Db\AbstractDbCommand;

/**
 * @property ManagerInterface $commandsManager
 */
abstract class AbstractCommand extends AbstractRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    private function internalHandle()
    {
        if ($this instanceof AbstractDbCommand)
        {
            try
            {
                $result = $this->execute();
            }
            catch (\YG\Phalcon\Cqrs\Command\Exception $ex) {
                $result = Result::fail($ex->getMessage(), $ex);
            }
            catch (Exception $ex)
            {
                $result = Result::fail('Hata!!!', $ex);
            }

            if ($result->isSuccess())
                $this->commandsManager->notifyEvent('success', $this, $result);
            else
                $this->commandsManager->notifyEvent('fail', $this, $result);

            return $result;
        }

        if ($this->getDI()->has(get_class($this)))
        {
            $commandHandler = $this->getDI()->get(get_class($this));

            if (!method_exists($commandHandler, 'handle'))
                throw new Exception('Not found "handle" method on the Command Handler Class');

            try
            {
                $result = $this->execute();
            }
            catch (Exception $ex)
            {
                $result = Result::fail('Hata!!!', $ex);
            }

            if ($result->isSuccess())
                $this->commandsManager->notifyEvent('success', $this, $result);
            else
                $this->commandsManager->notifyEvent('fail', $this, $result);

            return $result;
        }

        throw new Exception('Command handler not found **');
    }

    public static function __callStatic($name, $arguments)
    {
        if ($name == 'handle')
        {
            $instance = self::create($arguments[0] ?? [], $arguments[1] ?? []);
            $instance->assign($arguments[0] ?? [], $arguments[1] ?? []);
            return $instance->internalHandle();
        }

        return null;
    }

    public function __invoke()
    {
        return $this->internalHandle();
    }
}