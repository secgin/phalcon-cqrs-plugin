<?php

namespace YG\Phalcon\Cqrs\Query\Db\Handler;

use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;

abstract class AbstractQueryHandler implements InjectionAwareInterface
{
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

    public function __get($name)
    {
        if ($this->getDI()->has($name))
            return $this->getDI()->get($name);

        return null;
    }
}