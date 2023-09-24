<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use OutOfBoundsException;

final class Environment
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? null;

        if (func_num_args() === 1 && $value === null) {
            throw new OutOfBoundsException(sprintf('Environment variable "%s" is not defined', $key));
        }

        return $value ?? $default;
    }

    /**
     * @internal Nayleen\Async
     */
    public static function set(string $key, mixed $value): void
    {
        $_ENV[$key] = $_SERVER[$key] = $value;
    }
}
