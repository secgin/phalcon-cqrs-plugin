<?php

namespace YG\Phalcon\Query;

use ReflectionClass;

final class Reflection
{
    static public function getQueryHandlerMethodName(object $queryHandler, object $query): ?string
    {
        try
        {
            $reflection = new ReflectionClass($query);
            $methodName = ucfirst($reflection->getShortName());

            if (method_exists($queryHandler, $methodName))
                return $methodName;

            return null;
        }
        catch (\Exception $ex)
        {
            return null;
        }
    }
}