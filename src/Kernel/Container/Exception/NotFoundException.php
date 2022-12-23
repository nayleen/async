<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Container\Exception;

use OutOfBoundsException;
use Psr\Container\NotFoundExceptionInterface;

final class NotFoundException extends OutOfBoundsException implements NotFoundExceptionInterface
{
    public static function missingEnvironmentParameter(string $name): self
    {
        return new self();
    }

    /**
     * @param string|class-string $id
     */
    public static function notAutoloadable(string $id): self
    {
        return new self();
    }
}
