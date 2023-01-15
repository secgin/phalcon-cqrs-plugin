<?php

namespace YG\Phalcon\Cqrs\Query;

use Phalcon\Exception;
use YG\Phalcon\Cqrs\AbstractRequest;
use YG\Phalcon\Cqrs\Query\Db\AbstractDbQuery;

/**
 * @method static mixed handle(array $data = [], array $columnMap = [])
 */
abstract class AbstractQuery extends AbstractRequest
{
    public function __construct()
    {
        parent::__construct();
    }

    private function internalHandle()
    {
        if ($this instanceof AbstractDbQuery)
            return $this->fetch();
        elseif ($this->getDI()->has(get_class($this)))
        {
            $queryHandler = $this->getDI()->get(get_class($this));

            if (!method_exists($queryHandler, 'handle'))
                throw new Exception('Not found "handle" method on the Query Handler Class');

            return $queryHandler->handle($this);
        }

        throw new Exception('Not found Query Handler');
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