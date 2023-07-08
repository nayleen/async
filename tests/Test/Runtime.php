<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Amp\Cancellation;
use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime as BaseRuntime;

/**
 * @internal
 */
final class Runtime extends BaseRuntime
{
    public Kernel $kernel;

    protected function execute(Cancellation $cancellation): int
    {
        return 420;
    }
}
