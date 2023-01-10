<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Revolt\EventLoop\Driver as Loop;

abstract class Runtime
{
    public function __construct(private readonly Kernel $kernel)
    {

    }

    abstract protected function execute(): void;

    protected function setup(Loop $loop): void
    {
        $loop->onSignal(SIGHUP, $this->kernel->reload(...));
        $loop->onSignal(SIGINT, $this->kernel->stop(...));
        $loop->onSignal(SIGTERM, $this->kernel->stop(...));
    }

    final public function run(): void
    {
        $container = $this->kernel->boot();

        $this->setup($container->get(Loop::class));

        $this->kernel->run(function (Kernel $kernel) {
            $this->execute();
            $kernel->stop();
        });
    }
}
