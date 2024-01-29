<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\Sync\Channel;

/**
 * @psalm-internal Nayleen\Async
 *
 * @template-covariant TResult
 * @template TReceive
 * @template TSend
 */
abstract readonly class Runtime
{
    /**
     * @param Channel<TReceive, TSend>|null $channel
     */
    protected function initialize(?Channel $channel, ?Cancellation $cancellation): Kernel
    {
        return new Kernel(channel: $channel, cancellation: $cancellation);
    }

    /**
     * @psalm-internal Nayleen\Async
     * @return TResult
     */
    abstract public function execute(Kernel $kernel): mixed;

    /**
     * @param Channel<TReceive, TSend>|null $channel
     * @return TResult|null
     */
    final public function run(
        ?Channel $channel = null,
        ?Cancellation $cancellation = null,
    ): mixed {
        return $this->initialize($channel, $cancellation)->run($this->execute(...));
    }
}
