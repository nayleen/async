<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Runtime;

use Nayleen\Async\Kernel\Kernel;
use Nayleen\Async\Kernel\Runtime;
use Nayleen\Async\Worker\Worker as WorkerImplementation;
use Revolt\EventLoop;

final class Worker extends Runtime
{
    public function __construct(
        Kernel $kernel,
        private readonly WorkerImplementation $worker,
    ) {
        parent::__construct($kernel);
    }

    protected function execute(): void
    {
        $this->worker->run();
    }

    protected function setup(EventLoop\Driver $driver): void
    {
        parent::setup($driver);

        $this->worker->setup($driver);
    }
}
