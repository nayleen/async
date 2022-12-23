<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Container;

interface ServiceFactory
{
    /**
     * @template T
     *
     * @param non-empty-string|class-string<T> $id
     * @return T
     */
    public function make(string $id, array $parameters = []): object;
}
