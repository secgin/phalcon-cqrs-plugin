<?php

namespace YG\Phalcon\Cqrs\Query;

use Phalcon\Di;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * @method mixed|null handle()
 */
abstract class AbstractQuery
{
    public function assign(array $data, array $columnMap = [])
    {
        $reflection = new ReflectionClass($this);

        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property)
        {
            $name = $property->name;
            $fieldName = array_key_exists($name, $columnMap)
                ? $columnMap[$name]
                : $name;
            $value = $data[$fieldName] ?? null;

            $allowsNull = $property->getType()->allowsNull();

            if ($value == null and $allowsNull)
                $value = null;
            else
            {

                if (
                    $value == null and
                    $property->isDefault() and
                    (
                        $property->isProtected() or
                        (
                            $property->isPublic() and
                            $property->isInitialized($this)
                        )
                    )
                )
                    continue;

                $propertyTypeName = $property->getType()->getName();
                switch ($propertyTypeName)
                {
                    case 'bool':
                        $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                        settype($value, 'bool');
                        break;
                    case 'int':
                        $value = filter_var($value, FILTER_VALIDATE_INT);
                        settype($value, 'int');
                        break;
                    case 'float':
                        $value = filter_var($value, FILTER_VALIDATE_FLOAT);
                        settype($value, 'float');
                        break;
                    case 'string':
                        settype($value, 'string');
                        break;
                }
            }

            $this->$name = $value;
        }

    }

    public function getData(): array
    {
        $columnMap = null;
        if (method_exists($this, 'columnMap'))
            $columnMap = $this->columnMap();

        $reflection = new ReflectionClass($this);

        $data = [];
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property)
        {
            $propertyName = $property->getName();
            $propertyValue = $this->$propertyName ?? null;

            $fieldName = ($columnMap != null and array_key_exists($propertyName, $columnMap))
                ? $columnMap[$propertyName]
                : $propertyName;

            $data[$fieldName] = $propertyValue;
        }

        return $data;
    }

    static public function create(array $data = [], array $columnMap = []): self
    {
        $query = self::newInstance($data, $columnMap);
        $query->assign($data, $columnMap);

        return $query;
    }

    /**
     * @throws ReflectionException
     */
    static private function newInstance(array $data, array $columnMap = []): self
    {
        $reflection = new ReflectionClass(get_called_class());
        $constructorMethod = $reflection->getConstructor();

        $args = [];
        if ($constructorMethod)
        {
            foreach ($constructorMethod->getParameters() as $parameter)
            {
                $name = array_key_exists($parameter->name, $columnMap)
                    ? $columnMap[$parameter->name]
                    : $parameter->name;
                $value = $data[$name] ?? null;

                if ($value == null)
                {
                    $property = $reflection->getProperty($name);
                    $propertyType = $property->getType()->getName();
                    $allowsNull = $property->getType()->allowsNull();

                    if (!$allowsNull)
                    {
                        switch ($propertyType)
                        {
                            case 'bool':
                                $value = false;
                                break;
                            case 'int':
                            case 'float':
                                $value = 0;
                                break;
                            case 'string':
                                $value = '';
                                break;
                        }
                    }
                }
                else
                {
                    if ($value === '' and $propertyType != 'string')
                        $value = null;
                }

                $args[$name] = $value;
            }
        }

        return $reflection->newInstanceArgs($args);
    }

    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);
        if (method_exists($this, $methodName))
            return $this->$methodName();

        return property_exists($this, $name)
            ? $this->$name
            : null;
    }

    public function __set($name, $value)
    {
        $methodName = 'set' . ucfirst($name);
        if (method_exists($this, $methodName))
            $this->$methodName($value);
    }

    public function __call($name, $arguments)
    {
        if ($name == 'handle')
            return Di::getDefault()->get(QueryDispatcherProvider::$serviceName)->dispatch($this);

        return null;
    }
}