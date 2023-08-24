<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task\Scheduler;

use Amp\Future;
use Amp\Parallel\Worker\Task;
use Nayleen\Async\Task\Scheduler;

/**
 * @internal
 */
abstract class Decorator implements Scheduler
{
    /**
     * @internal
     */
    public readonly Scheduler $scheduler;

    public function __construct(Scheduler $scheduler)
    {
        $this->scheduler = $scheduler;
    }

    public function execute(Task $task, ?float $awaitTimeout = null, ?float $submitTimeout = null): mixed
    {
        return $this->scheduler->execute($task, $awaitTimeout, $submitTimeout);
    }

    public function kill(): void
    {
        $this->scheduler->kill();
    }

    public function shutdown(): void
    {
        $this->scheduler->shutdown();
    }

    public function submit(Task $task, ?float $timeout = null, bool $monitor = false): Future
    {
        return $this->scheduler->submit($task, $timeout, $monitor);
    }
}
