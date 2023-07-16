<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker;

use Nayleen\Async\Kernel;
use Nayleen\Async\Task as BaseTask;
use Nayleen\Async\Worker;

/**
 * @api
 */
class Task extends BaseTask
{
    /**
     * @param class-string<Worker> $worker
     */
    public function __construct(private readonly string $worker)
    {
    }

    protected function execute(Kernel $kernel): mixed
    {
        $worker = $kernel->container()->get($this->worker);
        assert($worker instanceof Worker);

        $worker->run($kernel);

        return null;
    }
}
