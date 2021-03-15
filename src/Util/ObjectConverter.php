<?php

namespace WebEtDesign\MailerBundle\Util;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

class ObjectConverter
{

    public static function convertToArray($object): array
    {
        if (!$object) {
            return [];
        }

        $values = [];

        $classRefex = new ReflectionClass($object);
        $methods    = $classRefex->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if (0 !== strpos($method->getName(), "get")) {
                continue;
            }
            $name          = lcfirst(str_replace('get', '', $method->getName()));
            $values[$name] = $object->{$method->getName()}();
        }

        return $values;
    }

    public static function getAvailableMethods($object)
    {
        if (!$object) {
            return [];
        }

        $methods = [];

        $classReflex = new ReflectionClass($object);

        foreach ($classReflex->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (0 !== strpos($method->getName(), "get")) {
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

    public static function getValue($field, $values)
    {
        if ($field === null) {
            return null;
        }

        if (is_object($values)) {
            $values = self::convertToArray($values);
        }

        $isArray = is_array($field);
        $field   = !$isArray ? [$field] : $field;
        foreach ($field as $k => $item) {
            if (!preg_match('/^__(.*)__$/', $item, $matches)) {
                continue;
            }

            unset($field[$k]);

            $split = explode('.', $matches[1]);
            $value = $values[array_shift($split)] ?? [];

            foreach ($split as $i) {
                $method = 'get' . ucfirst($i);
                if (!method_exists($value, $method)) {
                    $value = null;
                    break;
                }
                $value = $value->$method();
            }

            if ($value) {
                if (is_array($value)) {
                    $field = [...$field, ...$value];
                } else {
                    $field[] = $value;
                }
            }
        }

        return $isArray ? $field : array_pop($field);
    }
}
