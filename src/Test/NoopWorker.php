<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Amp\Cancellation;
use Nayleen\Async\Worker;

/**
 * @psalm-internal Nayleen\Async
 */
final class NoopWorker extends Worker
{
    protected function execute(Cancellation $cancellation): void
    {
    }
}
