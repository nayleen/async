<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task\Scheduler;

use Amp\DeferredFuture;
use Amp\Future;
use Amp\Parallel\Worker\Task;
use Nayleen\Async\Task\Scheduler;
use Throwable;

/**
 * @internal
 */
class RetryingScheduler extends Decorator
{
    public function __construct(Scheduler $scheduler, private readonly int $maxAttempts)
    {
        assert($maxAttempts > 0);

        parent::__construct($scheduler);
    }

    public function submit(Task $task, ?float $timeout = null, bool $monitor = false): Future
    {
        $deferredFuture = new DeferredFuture();

        parent::submit($task, $timeout, $monitor)->catch(
            function () use (&$deferredFuture, $task, $timeout, $monitor): void {
                $attempts = 1;

                retry:
                try {
                    $deferredFuture->complete($this->submit($task, $timeout, $monitor)->await());
                } catch (Throwable $throwable) {
                    if ($attempts++ <= $this->maxAttempts) {
                        goto retry;
                    }

                    $deferredFuture->error($throwable);
                }
            },
        );

        return $deferredFuture->getFuture()->ignore();
    }
}
