<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Application;
use Nayleen\Async\Kernel;

final readonly class TestApplication extends Application
{
    protected ?Kernel $kernel;

    public function __construct(?Kernel $kernel = null)
    {
        $this->kernel = $kernel;
        parent::__construct(static fn (): int => 420);
    }
}
