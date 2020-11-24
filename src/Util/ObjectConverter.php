<?php

namespace WebEtDesign\MailerBundle\Util;

use ReflectionClass;
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

}