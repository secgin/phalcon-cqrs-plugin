<?php

namespace YG\Phalcon\Cqrs\Command;

use Phalcon\Di;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * @method CommandResult handle()
 */
abstract class AbstractCommand
{
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

                if ($value === null)
                {
                    $value = $parameter->isDefaultValueAvailable()
                        ? $parameter->getDefaultValue()
                        : null;
                }

                if ($value === null and $parameter->allowsNull())
                {
                    $args[$name] = null;
                    continue;
                }

                $parameterTypeName = $parameter->getType()->getName() ?? null;
                switch ($parameterTypeName)
                {
                    case 'bool':
                        settype($value, 'bool');
                        break;
                    case 'int':
                        settype($value, 'int');
                        break;
                    case 'float':
                        settype($value, 'float');
                        break;
                    case 'array':
                        settype($value, 'array');
                        break;
                    case 'string':
                        settype($value, 'string');
                        break;
                }

                $args[$name] = $value;
            }
        }

        return $reflection->newInstanceArgs($args);
    }

    public function assign(array $data, array $columnMap = [])
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        foreach ($properties as $property)
        {
            $propertyName = $property->name;
            $propertyTypeName = null;
            $allowsNull = true;

            if ($property->getType() != null)
            {
                $propertyTypeName = $property->getType()->getName();
                $allowsNull = $property->getType()->allowsNull();
            }

            $fieldName = array_key_exists($propertyName, $columnMap)
                ? $columnMap[$propertyName]
                : $propertyName;


            if (!array_key_exists($fieldName, $data))
            {
                if (!$property->isPublic())
                    $property->setAccessible(true);

                if ($property->isInitialized($this))
                    continue;

                if ($allowsNull)
                {
                    $value = null;
                }
                else
                {
                    switch ($propertyTypeName)
                    {
                        case 'bool':
                            $value = false;
                            break;
                        case 'int':
                        case 'float':
                            $value = 0;
                            break;
                        case 'array':
                            $value = [];
                            break;
                        case 'string':
                            $value = '';
                            break;
                        default:
                            $value = null;
                    }
                }

                $this->$propertyName = $value;
                continue;
            }

            $value = $data[$fieldName];

            switch ($propertyTypeName)
            {
                case 'bool':
                    settype($value, 'bool');
                    break;
                case 'int':
                    settype($value, 'int');
                    break;
                case 'float':
                    settype($value, 'float');
                    break;
                case 'string':
                    settype($value, 'string');
                    break;
                case 'array':
                    settype($value, 'array');
                    break;
            }

            $this->$propertyName = $value;
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

    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);
        if (method_exists($this, $methodName))
            return $this->$methodName();

        return property_exists($this, $name)
            ? $this->$name
            : null;
    }

    public function __call($name, $arguments)
    {
        if ($name == 'handle')
            return Di::getDefault()->get(CommandDispatcherProvider::$serviceName)->dispatch($this);

        return null;
    }
}