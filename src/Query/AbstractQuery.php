<?php

namespace YG\Phalcon\Cqrs\Query;

use Error;
use Exception;
use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Throwable;
use YG\Phalcon\Cqrs\AbstractRequest;

/**
 * @method mixed fetch(array $data = [])
 */
abstract class AbstractQuery extends AbstractRequest implements Di\InjectionAwareInterface
{
    private function dispatch()
    {
        if ($this->getDI()->has(get_called_class()))
            return $this->getQueryDispatcher()->dispatch($this);

        if (method_exists($this, 'handle'))
        {
            $this->getQueryDispatcher()->notifyEvent('beforeFetch', $this);

            try
            {
                $result = $this->handle();
                $this->getQueryDispatcher()->notifyEvent('afterFetch', $this, $result);
                return $result;
            }
            catch (Exception|Error|Throwable $ex)
            {
                $this->getQueryDispatcher()->notifyEvent('error', $this, $ex);
                return null;
            }
        }

        return $this->getQueryDispatcher()->dispatch($this);
    }

    private function getQueryDispatcher(): DispatcherInterface
    {
        return $this->getDI()->get('queryDispatcher');
    }

    public static function __callStatic($name, $arguments)
    {
        if ($name == 'fetch')
        {
            $instance = self::create($arguments[0] ?? [], $arguments[1] ?? []);
            $instance->assign($arguments[0] ?? [], $arguments[1] ?? []);
            return $instance->dispatch();
        }

        return null;
    }

    #region Magic Methods
    public function __call($name, $arguments)
    {
        if ($name == 'fetch')
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

    public function __invoke()
    {
        return $this->dispatch();
    }
    #endregion

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