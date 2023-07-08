<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Execution;
use Nayleen\Async\Task\Scheduler;
use Nayleen\Async\Task\Status;
use SplObjectStorage;

use function Amp\async;

/**
 * @internal
 */
final class Tasks
{
    /**
     * @var SplObjectStorage<Task, ?Execution>
     */
    private SplObjectStorage $executions;

    private const DEFAULT_STATUS_INTERVAL = 2.0;

    public function __construct()
    {
        $this->executions = new SplObjectStorage();
    }

    private function submit(Scheduler $scheduler): void
    {
        foreach ($this->executions as $task => $execution) {
            $this->executions[$task] = match (Status::determine($execution)) {
                Status::RUNNING => $execution,
                default => $scheduler->submit($task),
            };
        }
    }

    public function add(Task ...$tasks): void
    {
        foreach ($tasks as $task) {
            $this->executions[$task] = null;
        }
    }

    public function schedule(Kernel $kernel, float|int $statusInterval = self::DEFAULT_STATUS_INTERVAL): void
    {
        if ($this->executions->count() === 0) {
            return;
        }

        $loop = $kernel->loop();

        $callbackId = $loop->unreference(
            $loop->repeat(
                $statusInterval,
                fn () => $this->submit($kernel->scheduler),
            ),
        );

        $kernel->cancellation()->subscribe(static fn () => $loop->cancel($callbackId));
    }
}
