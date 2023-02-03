<?php

declare(strict_types = 1);

namespace Nayleen\Async;

abstract class Runtime
{
    public function __construct(protected readonly Kernel $kernel)
    {

    }

    /**
     * @return static
     */
    public static function create(Kernel $kernel = new Kernel()): Runtime
    {
        return $kernel->make(static::class);
    }

    abstract public function run(): int;
}
