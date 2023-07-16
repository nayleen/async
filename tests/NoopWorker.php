<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;

/**
 * @internal
 */
final class NoopWorker extends Worker
{
    protected function execute(Cancellation $cancellation): void
    {
    }
}
