<?php

declare(strict_types = 1);

namespace Nayleen\Async;

/**
 * @internal
 */
final class TestTask extends Task
{
    protected function execute(Kernel $kernel): int
    {
        return 69;
    }
}
