<?php

namespace WebEtDesign\MailerBundle\Util;

use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionUnionType;

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
                $ret = '';
                $returnType = $method->getReturnType();
                if ($returnType instanceof ReflectionUnionType) {
                    foreach ($returnType->getTypes() as $index => $type) {
                        $ret .= ($index !== 0 ? ' | ' : '') . $type->getName();
                    }
                } else {
                    $ret = $method->getReturnType()->getName() . ($method->getReturnType()->allowsNull() ? '|null' : '');
                }

            }

            $methods[lcfirst(str_replace('get', '', $method->getName()))] = $ret;
        }

        return $methods;
    }

}
