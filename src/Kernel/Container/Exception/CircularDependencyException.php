<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Container\Exception;

use LogicException;
use Psr\Container\ContainerExceptionInterface;

final class CircularDependencyException extends LogicException implements ContainerExceptionInterface
{
    /**
     * @param class-string $class
     * @param class-string $dependency
     */
    public function __construct(string $class, string $dependency)
    {
        parent::__construct(
            sprintf(
                'Circular dependency "%s" for service "%s" detected.',
                $dependency,
                $class,
            ),
        );
    }
}
