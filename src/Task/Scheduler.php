<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\CompositeCancellation;
use Amp\NullCancellation;
use Amp\Parallel\Worker\Execution;
use Amp\Parallel\Worker\Task;
use Amp\Parallel\Worker\WorkerPool;
use Amp\TimeoutCancellation;
use Nayleen\Async\Kernel;

/**
 * @internal
 */
final class Scheduler
{
    public function __construct(private readonly Kernel $kernel)
    {

    }

    private function pool(): WorkerPool
    {
        return $this->kernel->container()->get(WorkerPool::class);
    }

    public function execute(
        Task $task,
        ?float $awaitTimeout = null,
        ?float $submitTimeout = null,
    ): mixed {
        assert($awaitTimeout === null || $awaitTimeout >= 0);

        $cancellation = isset($awaitTimeout) ? new TimeoutCancellation($awaitTimeout) : new NullCancellation();
        $cancellation = new CompositeCancellation($this->kernel->cancellation(), $cancellation);

        return $this->submit($task, $submitTimeout)->await($cancellation);
    }

    public function kill(): void
    {
        $this->pool()->kill();
    }

    public function running(): bool
    {
        return $this->pool()->isRunning();
    }

    public function shutdown(): void
    {
        $this->pool()->shutdown();
    }

    public function submit(Task $task, ?float $timeout = null): Execution
    {
        assert($timeout === null || $timeout >= 0);

        $cancellation = isset($timeout) ? new TimeoutCancellation($timeout) : new NullCancellation();
        $cancellation = new CompositeCancellation($this->kernel->cancellation(), $cancellation);

        return $this->pool()->getWorker()->submit($task, $cancellation);
    }
}
