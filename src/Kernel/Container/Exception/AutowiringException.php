<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Container\Exception;

use DomainException;
use Psr\Container\ContainerExceptionInterface;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionUnionType;

final class AutowiringException extends DomainException implements ContainerExceptionInterface
{
    /**
     * @param class-string $class
     * @param ReflectionIntersectionType|ReflectionUnionType $type
     */
    public static function cannotResolveCombinedType(
        string $class,
        ReflectionIntersectionType|ReflectionUnionType $type
    ): self {
        return new self();
    }

    /**
     * @return static
     */
    public static function privateConstructor(ReflectionClass $class): self
    {
        return new self();
    }
}
