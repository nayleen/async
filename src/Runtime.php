<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;
use Amp\NullCancellation;
use Amp\Parallel\Worker\Task;
use Amp\Sync\Channel;

use function Amp\async;

/**
 * @api
 */
abstract class Runtime implements Task
{
    abstract protected function execute(Kernel $kernel): mixed;

    public function run(?Channel $channel = null, Cancellation $cancellation = new NullCancellation()): mixed
    {
        $kernel = new Kernel(channel: $channel, cancellation: $cancellation);

        return $kernel->run(fn () => async($this->execute(...), $kernel));
    }
}
