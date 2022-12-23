<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Container;

use Closure;
use Psr\Container\ContainerInterface;

/**
 * @api
 */
interface ServiceProvider extends ContainerInterface
{
    /**
     * @param non-empty-string $alias
     * @param non-empty-string $originalId
     */
    public function alias(string $alias, string $originalId): self;

    /**
     * @param non-empty-string $param
     */
    public function env(string $param, string $default = null): string;

    /**
     * @param non-empty-string $id
     */
    public function factory(string $id, Closure $factory): self;

    /**
     * @template T
     *
     * @param non-empty-string|class-string<T> $id
     * @return mixed|T
     */
    public function get(string $id): mixed;

    /**
     * @template T
     *
     * @param non-empty-string|class-string<T> $id
     */
    public function has(string $id): bool;

    /**
     * @param non-empty-string $id
     */
    public function set(string $id, mixed $value): self;
}
