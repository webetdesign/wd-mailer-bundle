<?php

namespace WebEtDesign\MailerBundle\Util;

use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class ObjectConverter
{

    /**
     * @throws ReflectionException
     */
    public static function convertToArray($object): array
    {
        if (!$object) {
            return [];
        }

        $values = [];

        $classReflex = new ReflectionClass($object);
        $methods    = $classReflex->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (!str_starts_with($method->getName(), "get")) {
                continue;
            }
            $name          = lcfirst(str_replace('get', '', $method->getName()));
            $values[$name] = $object->{$method->getName()}();
        }

        return $values;
    }

    /**
     * @throws ReflectionException
     */
    public static function getAvailableMethods($object): array
    {
        if (!$object) {
            return [];
        }

        $methods = [];

        $classReflex = new ReflectionClass($object);

        foreach ($classReflex->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (!str_starts_with($method->getName(), "get")) {
                continue;
            }

            $ret = 'Undefined';
            if ($method->hasReturnType()) {
                $ret = $method->getReturnType()->getName() . ($method->getReturnType()->allowsNull() ? '|null' : '');
            }

            $methods[lcfirst(str_replace('get', '', $method->getName()))] = $ret;
        }

        return $methods;
    }

}
