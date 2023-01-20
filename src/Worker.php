<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime;
use Nayleen\Async\Worker\Worker as WorkerImplementation;

/**
 * @api
 */
final class Worker extends Runtime
{
    public function __construct(
        Kernel $kernel,
        private readonly WorkerImplementation $worker
    ) {
        parent::__construct($kernel);
    }

    protected function execute(): void
    {
        $this->worker->run($this->kernel);
    }
}
