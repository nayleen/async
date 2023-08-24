<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Cancellation;
use Amp\Future;
use Amp\NullCancellation;
use Amp\Parallel\Worker\Execution as AmpExecution;
use Amp\Parallel\Worker\Task;
use Amp\Parallel\Worker\Worker;
use Amp\Parallel\Worker\WorkerPool;
use Nayleen\Async\Kernel;

/**
 * @internal
 */
class Execution
{
    private string $callbackId;

    private ?AmpExecution $execution = null;

    public function __construct(private readonly Kernel $kernel, public readonly Task $task)
    {
    }

    public function __destruct()
    {
        $this->cancel();
    }

    private function monitor(): void
    {
        match ($this->state()) {
            State::RUNNING => null,
            default => $this->start(),
        };
    }

    private function worker(): Worker
    {
        return $this->kernel->container()->get(WorkerPool::class)->getWorker();
    }

    public function cancel(): void
    {
        if (!isset($this->execution) || $this->execution->getChannel()->isClosed()) {
            return;
        }

        if (isset($this->callbackId)) {
            $this->kernel->loop()->cancel($this->callbackId);
        }

        $this->execution->getChannel()->close();
    }

    public function start(
        Cancellation $cancellation = new NullCancellation(),
        float|int|null $monitorInterval = null,
    ): Future {
        $this->execution = $this->worker()->submit($this->task, $cancellation);

        if (isset($monitorInterval)) {
            $this->callbackId = $this->kernel->loop()->repeat($monitorInterval, $this->monitor(...));
        }

        return $this->execution->getFuture()->ignore();
    }

    public function state(): State
    {
        return match (true) {
            !isset($this->execution) => State::STARTING,
            $this->execution->getChannel()->isClosed() => State::FINISHED,
            default => State::RUNNING,
        };
    }
}
