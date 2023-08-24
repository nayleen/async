<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task\Scheduler;

use Amp\Future;
use Amp\Parallel\Worker\Task;
use Closure;
use Nayleen\Async\Task\Scheduler;
use Throwable;

/**
 * @internal
 */
class ErrorHandlingScheduler extends Decorator
{
    /**
     * @param Closure(Throwable): void $errorHandler
     */
    public function __construct(Scheduler $scheduler, private readonly Closure $errorHandler)
    {
        parent::__construct($scheduler);
    }

    public function submit(Task $task, ?float $timeout = null, bool $monitor = false): Future
    {
        return parent::submit($task, $timeout, $monitor)->catch($this->errorHandler);
    }
}
