<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Kernel\Kernel;
use Revolt\EventLoop;

abstract class Runtime
{
    public function __construct(private readonly Kernel $kernel)
    {

    }

    final public static function create(Kernel $kernel, array $parameters = []): static
    {
        return $kernel->create(static::class, $parameters);
    }

    abstract protected function execute(): ?int;

    protected function setup(EventLoop\Driver $driver): void
    {

    }

    final public function run(): int
    {
        $this->setup($this->kernel->boot()->get(EventLoop\Driver::class));

        $this->kernel->run(function () use (&$exitCode) {
            $exitCode = (int) $this->execute();
        });

        return $exitCode;
    }
}
