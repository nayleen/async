<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\NullCancellation;
use Amp\Sync\Channel;

/**
 * @internal
 *
 * @template-covariant TResult
 * @template TReceive
 * @template TSend
 */
abstract class Runtime
{
    /**
     * @psalm-internal Nayleen\Async
     */
    public Kernel $kernel;

    /**
     * @return TResult
     */
    abstract protected function execute(Kernel $kernel): mixed;

    /**
     * @param Channel<TReceive, TSend>|null $channel
     */
    protected function initialize(?Channel $channel, Cancellation $cancellation): Kernel
    {
        return $this->kernel ??= new Kernel(channel: $channel, cancellation: $cancellation);
    }

    /**
     * @param Channel<TReceive, TSend>|null $channel
     * @return TResult
     */
    final public function run(
        ?Channel $channel = null,
        Cancellation $cancellation = new NullCancellation(),
    ): mixed {
        return $this->initialize($channel, $cancellation)->run($this->execute(...));
    }
}
