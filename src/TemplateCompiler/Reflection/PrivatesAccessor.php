<?php

declare(strict_types=1);

namespace TomasVotruba\Bladestan\TemplateCompiler\Reflection;

use ReflectionProperty;
use RuntimeException;

final class PrivatesAccessor
{
    public function getPrivateProperty(object $object, string $propertyName): mixed
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(true);

        return $propertyReflection->getValue($object);
    }

    private function resolvePropertyReflection(object $object, string $propertyName): ReflectionProperty
    {
        if (property_exists($object, $propertyName)) {
            return new ReflectionProperty($object, $propertyName);
        }

        $parentClass = get_parent_class($object);
        if ($parentClass !== false) {
            return new ReflectionProperty($parentClass, $propertyName);
        }

        $errorMessage = sprintf('Property "$%s" was not found in "%s" class', $propertyName, $object::class);
        throw new RuntimeException($errorMessage);
    }

    public function setPrivateProperty(object $object, string $propertyName, mixed $value): void
    {
        $propertyReflection = $this->resolvePropertyReflection($object, $propertyName);
        $propertyReflection->setAccessible(true);

        $propertyReflection->setValue($object, $value);
    }
}
