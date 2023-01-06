<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Revolt\EventLoop\Driver;
use Nayleen\Async\Worker\Worker as WorkerImplementation;

final class Worker implements Runtime
{
    public function __construct(
        private readonly Driver $loop,
        private readonly WorkerImplementation $worker,
    ) {
    }

    public function run(): int
    {
        $this->worker->setup($this->loop);

        return (new Loop($this->loop))->run(fn () => $this->worker->run());
    }
}
