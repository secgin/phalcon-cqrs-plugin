<?php

namespace YG\Phalcon\Cqrs;

use Phalcon\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Http\RequestInterface;
use ReflectionClass;
use ReflectionProperty;

abstract class AbstractRequest implements InjectionAwareInterface
{
    final static private function newInstance(array $data, array $columnMap = []): self
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

    final static public function create(array $data = [], array $columnMap = []): self
    {
        $instance = self::newInstance($data, $columnMap);
        $instance->assign($data, $columnMap);
        return $instance;
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
            $isBuiltinPropertyType = true;

            if ($property->getType() != null)
            {
                $isBuiltinPropertyType = $property->getType()->isBuiltin();
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

                if (!$isBuiltinPropertyType)
                    $this->setPropertyValueOfUnBuiltin($propertyName, $propertyTypeName, $allowsNull);
                else
                    $this->$propertyName = $allowsNull
                        ? null
                        : $this->getPropertyDefaultValue($propertyTypeName);

                continue;
            }

            $value = $data[$fieldName];

            if ($allowsNull and $value == '')
                $value = null;
            else
                $this->setValueTypeByPropertyType($value, $propertyTypeName);

            if ($isBuiltinPropertyType)
                $this->$propertyName = $value;
            else
                $this->setPropertyValueOfUnBuiltin($propertyName, $propertyTypeName, $allowsNull);
        }
    }

    private function getPropertyDefaultValue(?string $propertyTypeName)
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

        return $value;
    }

    private function setValueTypeByPropertyType(&$value, ?string $propertyTypeName): void
    {
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
    }

    private function setPropertyValueOfUnBuiltin(string $propertyName, string $propertyTypeName, bool $allowsNull): void
    {
        $unBuiltinValue = null;
        if ($propertyTypeName == 'Phalcon\Http\Request\File')
        {
            /**
             * @var RequestInterface $request
             */
            $request = $this->getDI()->get('request');
            $files = $request->getUploadedFiles(true, true);
            $unBuiltinValue = $files[$propertyName] ?? null;
        }

        if ($unBuiltinValue != null)
            $this->$propertyName = $unBuiltinValue;
        elseif ($allowsNull)
            $this->$propertyName = null;
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
            if ($property->getDeclaringClass()->getName() != get_class($this))
                continue;

            $propertyName = $property->getName();

            $propertyTypeName = null;
            if ($property->getType() != null)
                $propertyTypeName = $property->getType()->getName();

            $propertyValue = $this->$propertyName ?? null;

            switch ($propertyTypeName)
            {
                case 'bool':
                    $propertyValue = $propertyValue ? 1 : 0;
                    break;
            }

            $fieldName = ($columnMap != null and array_key_exists($propertyName, $columnMap))
                ? $columnMap[$propertyName]
                : $propertyName;

            $data[$fieldName] = $propertyValue;
        }
        return $data;
    }

    #region Magic Methods
    public function __get($name)
    {
        $methodName = 'get' . ucfirst($name);
        if (method_exists($this, $methodName))
            return $this->$methodName();

        if ($this->getDI()->has($name))
            return $this->getDI()->get($name);

        return null;
    }

    public function __set($name, $value)
    {
        $methodName = 'set' . ucfirst($name);
        if (method_exists($this, $methodName))
            $this->$methodName($value);
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