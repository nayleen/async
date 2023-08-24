<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task\Scheduler;

use Closure;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task\Scheduler as SchedulerInterface;
use Throwable;

/**
 * @internal
 */
class Builder
{
    /**
     * @var Closure(Throwable): void|null
     */
    private ?Closure $errorHandler = null;

    private ?int $maxAttempts = null;

    private float|int $monitorInterval = DefaultScheduler::MONITOR_INTERVAL_DEFAULT;

    public function __construct(private readonly Kernel $kernel)
    {
    }

    public function build(): SchedulerInterface
    {
        $scheduler = new DefaultScheduler($this->kernel, $this->monitorInterval);

        if (isset($this->maxAttempts)) {
            $scheduler = new RetryingScheduler($scheduler, $this->maxAttempts);
        }

        if (isset($this->errorHandler)) {
            $scheduler = new ErrorHandlingScheduler($scheduler, $this->errorHandler);
        }

        return $scheduler;
    }

    public function withErrorHandler(Closure $errorHandler): self
    {
        $copy = clone $this;
        $copy->errorHandler = $errorHandler;

        return $copy;
    }

    public function withMaxAttempts(int $maxAttempts): self
    {
        assert($maxAttempts > 0);

        $copy = clone $this;
        $copy->maxAttempts = $maxAttempts;

        return $copy;
    }

    public function withMonitorInterval(float|int $monitorInterval): self
    {
        $copy = clone $this;
        $copy->monitorInterval = $monitorInterval;

        return $copy;
    }
}
