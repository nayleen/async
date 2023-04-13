<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker;

use Amp\Cancellation;
use Amp\Parallel\Worker\Task as TaskInterface;
use Amp\Sync\Channel;
use Nayleen\Async\Kernel;
use Nayleen\Async\Worker;

final class Task implements TaskInterface
{
    /**
     * @param class-string<Worker> $worker
     */
    public function __construct(private readonly string $worker)
    {
    }

    public function run(Channel $channel, Cancellation $cancellation): mixed
    {
        $kernel = new Kernel(channel: $channel, cancellation: $cancellation);

        $worker = $kernel->make($this->worker);
        assert($worker instanceof Worker);

        $worker->run($kernel);

        return null;
    }
}
