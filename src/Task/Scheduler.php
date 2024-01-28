<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Cancellation;
use Amp\CompositeCancellation;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Amp\Future;
use Amp\NullCancellation;
use Amp\Parallel\Worker\Execution;
use Amp\Parallel\Worker\Task as AmpTask;
use Amp\Parallel\Worker\Worker;
use Amp\Parallel\Worker\WorkerPool;
use Amp\TimeoutCancellation;
use Closure;
use Nayleen\Async\Kernel;
use SplObjectStorage;
use Throwable;

use function Amp\delay;

/**
 * @psalm-internal Nayleen\Async
 */
class Scheduler
{
    use ForbidCloning;
    use ForbidSerialization;

    /**
     * @var SplObjectStorage<AmpTask, Execution>
     */
    private SplObjectStorage $executions;

    public const RETRY_COUNT_DEFAULT = 2;

    public const RETRY_DELAY_DEFAULT = 2.0; // seconds

    public function __construct(
        private readonly Kernel $kernel,
        private readonly int $retryCount = self::RETRY_COUNT_DEFAULT,
        private readonly float|int $retryDelay = self::RETRY_DELAY_DEFAULT,
    ) {
        $this->executions = new SplObjectStorage();
    }

    private function cancel(AmpTask $task): void
    {
        if (!$this->executions->offsetExists($task)) {
            return;
        }

        $execution = $this->executions->offsetGet($task);

        if ($execution->getFuture()->isComplete() || $execution->getChannel()->isClosed()) {
            return;
        }

        $execution->getFuture()->ignore();
        $execution->getChannel()->close();
    }

    private function cancellation(?float $timeout): Cancellation
    {
        assert($timeout === null || $timeout >= 0);

        return new CompositeCancellation(
            $this->kernel->cancellation,
            isset($timeout) ? new TimeoutCancellation($timeout) : new NullCancellation(),
        );
    }

    private function retry(AmpTask $task, Cancellation $cancellation, int $attempts = 1): mixed
    {
        try {
            delay($this->retryDelay * $attempts, false, $cancellation);

            return $this->spawn($task, $cancellation)->await($cancellation);
        } catch (Throwable) {
            if ($attempts++ <= $this->retryCount) {
                return $this->retry($task, $cancellation, $attempts);
            }
        }

        return null;
    }

    private function spawn(AmpTask $task, Cancellation $cancellation): Execution
    {
        $this->cancel($task);

        $execution = $this->worker()->submit($task, $cancellation);
        $execution->getChannel()->onClose(fn () => $this->kernel->loop()->queue($this->cancel(...), $task));

        $this->executions->offsetSet($task, $execution);

        return $execution;
    }

    private function worker(): Worker
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
    public function run(AmpTask|Closure|string $task, ?float $awaitTimeout = null, ?float $submitTimeout = null): mixed
    {
        return $this->submit($task, timeout: $submitTimeout)->await($this->cancellation($awaitTimeout));
    }

    public function shutdown(): void
    {
        $this->workers()->shutdown();
    }

    /**
     * @api
     */
    public function submit(AmpTask|Closure|string $task, ?float $timeout = null): Future
    {
        assert($task !== '');

        $task = match (true) {
            $task instanceof Closure => new AnonymousTask($task),
            is_string($task) => AnonymousTask::fromScript($task),
            default => $task,
        };

        $cancellation = $this->cancellation($timeout);

        $future = $this->spawn($task, $cancellation)->getFuture();

        return $future->catch(fn () => $this->retry($task, $cancellation));
    }
}
