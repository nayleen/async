<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Application;
use Nayleen\Async\Kernel;

final readonly class TestApplication extends Application
{
    public function execute(Kernel $kernel): int
    {
        return 420;
    }
}
