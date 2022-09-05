<?php

namespace YG\Phalcon\Command;

use ReflectionClass;

final class Reflection
{
    static public function getCommandHandlerMethodName(object $commandHandler, object $command): ?string
    {
        try
        {
            $reflection = new ReflectionClass($command);
            $methodName = ucfirst($reflection->getShortName());

            if (method_exists($commandHandler, $methodName))
                return $methodName;

            return null;
        }
        catch (\Exception $ex)
        {
            return null;
        }
    }
}