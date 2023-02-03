<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Amp\Parallel\Worker\WorkerPool;
use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime;

final class AppServer extends Runtime
{
    private function __construct(
        Kernel $kernel,
        private readonly WorkerPool $workerPool,
    ) {
        parent::__construct($kernel);
    }

    public function create(
        Kernel $kernel = new Kernel(),
    ): AppServer {
        return new self(
            $kernel,

        );
    }

    public function run(): int
    {
        // TODO: Implement run() method.
    }
}
