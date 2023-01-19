<?php

namespace YG\Phalcon\Cqrs\Command;

use Error;
use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Exception;
use Throwable;
use YG\Phalcon\Cqrs\AbstractRequest;

/**
 * @method CommandResultInterface execute
 * @method static AbstractCommand create(array $data = [], array $columnMap = [])
 */
abstract class AbstractCommand extends AbstractRequest implements Di\InjectionAwareInterface
{
    private function dispatch(): CommandResultInterface
    {
        if ($this->getDI()->has(get_called_class()))
            return $this->getCommandDispatcher()->dispatch($this);

        if (method_exists($this, 'handle'))
        {
            try
            {
                $this->getCommandDispatcher()->notifyEvent('beforeExecute', $this);
                $result = $this->handle();
                $this->getCommandDispatcher()->notifyEvent('afterExecute', $this, $result);
            }
            catch (Exception|Error|Throwable $ex)
            {
                $result = CommandResult::fail('İşlem sırasında hata oluştu.');
                $this->getCommandDispatcher()->notifyEvent('error', $this, $ex);
            }

            return $result;
        }

        return $this->getCommandDispatcher()->dispatch($this);
    }

    private function getCommandDispatcher(): DispatcherInterface
    {
        return Di::getDefault()->get('commandDispatcher');
    }

    public function __call($name, $arguments)
    {
        if ($name == 'execute')
            return $this->dispatch();

        return null;
    }

    public function __get($name)
    {
        $result = parent::__get($name);

        if ($result == null and $this->getDI()->has($name))
            return $this->getDI()->get($name);

        return $result;
    }

    public function __invoke(): CommandResultInterface
    {
        return $this->dispatch();
    }

    #region InjectionAwareInterface
    private DiInterface $container;

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
    #endregion
}