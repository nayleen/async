<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task\Scheduler;

use Amp\Cancellation;
use Amp\CompositeCancellation;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Amp\Future;
use Amp\NullCancellation;
use Amp\Parallel\Worker\Task;
use Amp\Parallel\Worker\WorkerPool;
use Amp\TimeoutCancellation;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task\Execution;
use Nayleen\Async\Task\Scheduler;
use SplObjectStorage;

/**
 * @internal
 */
class DefaultScheduler implements Scheduler
{
    use ForbidCloning;
    use ForbidSerialization;

    /**
     * @var SplObjectStorage<Task, Execution>
     */
    private SplObjectStorage $executions;

    public const MONITOR_INTERVAL_DEFAULT = 2.0; // seconds

    public function __construct(
        private readonly Kernel $kernel,
        private readonly float|int $monitorInterval = self::MONITOR_INTERVAL_DEFAULT,
    ) {
        $this->executions = new SplObjectStorage();
    }

    private function cancel(Task $task): void
    {
        if (!$this->executions->offsetExists($task)) {
            return;
        }

        $execution = $this->executions->offsetGet($task);
        $execution->cancel();

        $this->executions->offsetUnset($task);
    }

    private function cancellation(?float $timeout): Cancellation
    {
        assert($timeout === null || $timeout >= 0);

        return new CompositeCancellation(
            $this->kernel->cancellation,
            isset($timeout) ? new TimeoutCancellation($timeout) : new NullCancellation(),
        );
    }

    private function workers(): WorkerPool
    {
        return $this->kernel->container()->get(WorkerPool::class);
    }

    public function execute(Task $task, ?float $awaitTimeout = null, ?float $submitTimeout = null): mixed
    {
        return $this->submit($task, timeout: $submitTimeout)->await($this->cancellation($awaitTimeout));
    }

    public function kill(): void
    {
        $this->workers()->kill();
    }

    public function shutdown(): void
    {
        $this->workers()->shutdown();
    }

    public function submit(Task $task, ?float $timeout = null, bool $monitor = false): Future
    {
        $this->cancel($task);

        $execution = new Execution($this->kernel, $task);
        $this->executions->offsetSet($task, $execution);

        return $execution->start($this->cancellation($timeout), $monitor ? $this->monitorInterval : null);
    }
}
