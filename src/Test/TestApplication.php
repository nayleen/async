<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Application;

final readonly class TestApplication extends Application
{
    public function __construct()
    {
        parent::__construct(static fn (): int => 420);
    }
}
