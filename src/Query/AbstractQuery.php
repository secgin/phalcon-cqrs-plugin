<?php

namespace YG\Phalcon\Cqrs\Query;

use YG\Phalcon\Cqrs\AbstractRequest;
use YG\Phalcon\Cqrs\NotFoundException;
use YG\Phalcon\Cqrs\Query\Db\AbstractDbQuery;

/**
 * @method static mixed handle(array $data = [], array $columnMap = [])
 */
abstract class AbstractQuery extends AbstractRequest
{
    /**
     * @throws NotFoundException
     */
    private function dispatch()
    {
        if ($this->getDI()->has(get_class($this)))
        {
            $queryHandler = $this->getDI()->get(get_class($this));

            if (!method_exists($queryHandler, 'handle'))
                throw new NotFoundException(
                    'Not found "handle" method on the query handler class ' .
                    'for execute the  ' . get_called_class() . ' query');

            return $queryHandler->handle($this);
        }

        if ($this instanceof AbstractDbQuery)
            return $this->fetch();

        throw new NotFoundException('Not found Query Handler for ' . get_called_class() . ' query');
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