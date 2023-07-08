<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\NullCancellation;
use Amp\Sync\Channel;

/**
 * @api
 */
abstract class Runtime
{
    public readonly Tasks $tasks;

    protected Kernel $kernel;

    public function __construct()
    {
        $this->tasks = new Tasks();
    }

    abstract protected function execute(Cancellation $cancellation): mixed;

    final public function run(?Channel $channel = null, Cancellation $cancellation = new NullCancellation()): mixed
    {
        $this->kernel ??= new Kernel(channel: $channel, cancellation: $cancellation);
        $this->tasks->schedule($this->kernel);

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
