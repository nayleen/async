<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Cancellation;
use Amp\CompositeCancellation;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Amp\NullCancellation;
use Amp\Parallel\Worker\Execution;
use Amp\Parallel\Worker\Task as TaskInterface;
use Amp\Parallel\Worker\Worker as AmpWorker;
use Amp\Parallel\Worker\WorkerPool;
use Amp\TimeoutCancellation;
use Closure;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task;
use SplObjectStorage;

/**
 * @psalm-internal Nayleen\Async
 */
class Scheduler
{
    use ForbidCloning;
    use ForbidSerialization;

    /**
     * @var SplObjectStorage<TaskInterface, Execution>
     */
    private readonly SplObjectStorage $executions;

    public function __construct(private readonly Kernel $kernel)
    {
        $this->executions = new SplObjectStorage();
    }

    private function cancel(TaskInterface $task): void
    {
        if (!$this->executions->offsetExists($task)) {
            return;
        }

        $execution = $this->executions->offsetGet($task);

        try {
            if (
                $execution->getFuture()->isComplete()
                || $execution->getChannel()->isClosed()
            ) {
                return;
            }

            $execution->getFuture()->ignore();
            $execution->getChannel()->close();
        } finally {
            $this->executions->offsetUnset($task);
        }
    }

    private function cancellation(?float $timeout): Cancellation
    {
        assert($timeout === null || $timeout >= 0);

        if (!isset($timeout)) {
            return $this->kernel->cancellation;
        }

        return new CompositeCancellation($this->kernel->cancellation, new TimeoutCancellation($timeout));
    }

    private function spawn(TaskInterface $task, Cancellation $cancellation = new NullCancellation()): Execution
    {
        $this->cancel($task);

        $execution = $this->worker()->submit($task, $cancellation);
        $execution->getChannel()->onClose(fn () => $this->cancel($task));

        $this->executions->offsetSet($task, $execution);

        return $execution;
    }

    private function worker(): AmpWorker
    {
        return $this->workers()->getWorker();
    }

    private function workers(): WorkerPool
    {
        return $this->kernel->container()->get(WorkerPool::class);
    }

    public function kill(): void
    {
        $this->workers()->kill();
    }

    /**
     * @api
     */
    public function run(Closure|string|TaskInterface $task, ?float $timeout = null): mixed
    {
        return $this->submit($task)->await($this->cancellation($timeout));
    }

    public function shutdown(): void
    {
        $this->workers()->shutdown();
    }

    /**
     * @api
     */
    public function submit(Closure|string|TaskInterface $task): Execution
    {
        return $this->spawn(Task::create($task));
    }
}
