<?php

declare(strict_types = 1);

namespace Nayleen\Async;

/**
 * @internal
 */
final class TestApplication extends Application
{
    protected function execute(Kernel $kernel): int
    {
        return 420;
    }
}
