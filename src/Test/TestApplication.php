<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Application;
use Nayleen\Async\Kernel;

final class TestApplication extends Application
{
    protected function execute(Kernel $kernel): int
    {
        return 420;
    }
}
