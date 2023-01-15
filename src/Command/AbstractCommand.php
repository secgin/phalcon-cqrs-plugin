<?php

namespace YG\Phalcon\Cqrs\Command;

use Phalcon\Exception;
use YG\Phalcon\Cqrs\AbstractRequest;
use YG\Phalcon\Cqrs\Command\Db\AbstractDbCommand;
use YG\Phalcon\Cqrs\NotFoundException;

/**
 * @property ManagerInterface $commandsManager
 */
abstract class AbstractCommand extends AbstractRequest
{
    private function dispatch()
    {
        if ($this->getDI()->has(get_class($this)))
        {
            $commandHandler = $this->getDI()->get(get_class($this));

            if (!method_exists($commandHandler, 'handle'))
                throw new NotFoundException(
                    'Not found "handle" method on the command handler class ' .
                    'for execute the  ' . get_called_class() . ' command');

            try
            {
                $result = $commandHandler->handle($this);
            }
            catch (Exception $ex)
            {
                $result = CommandResult::fail('Hata!!!', $ex);
            }
        }
        elseif ($this instanceof AbstractDbCommand)
        {
            try
            {
                $result = $this->execute();
            }
            catch (Exception $ex)
            {
                $result = CommandResult::fail('Hata!!!', $ex);
            }
        }
        else
            throw new NotFoundException('Not found Command Handler for ' . get_called_class() . ' command');

        if ($result->isSuccess())
            $this->commandsManager->notifyEvent('success', $this, $result);
        else
            $this->commandsManager->notifyEvent('fail', $this, $result);

        return $result;
    }

    public static function __callStatic($name, $arguments)
    {
        if ($name == 'handle')
        {
            $instance = self::create($arguments[0] ?? [], $arguments[1] ?? []);
            $instance->assign($arguments[0] ?? [], $arguments[1] ?? []);
            return $instance->dispatch();
        }

        return null;
    }

    public function __invoke()
    {
        return $this->dispatch();
    }
}