<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Kernel;
use Nayleen\Async\Task;

/**
 * @psalm-internal Nayleen\Async
 */
final class TestTask extends Task
{
    protected function execute(Kernel $kernel): int
    {
        return 69;
    }
}
