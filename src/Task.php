<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\NullCancellation;
use Amp\Parallel\Worker\Task as TaskInterface;
use Amp\Sync\Channel;

abstract class Task implements TaskInterface
{
    protected Kernel $kernel;

    abstract protected function execute(Cancellation $cancellation): mixed;

    final public function run(?Channel $channel = null, Cancellation $cancellation = new NullCancellation()): mixed
    {
        $this->kernel ??= new Kernel(channel: $channel, cancellation: $cancellation);

        return $this->execute($this->kernel->cancellation());
    }

    /**
     * @internal
     */
    final public function withKernel(Kernel $kernel): static
    {
        $copy = clone $this;
        $copy->kernel = $kernel;

        return $copy;
    }
}
