<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Kernel;
use Nayleen\Async\Timers;
use Nayleen\Async\Worker;

final readonly class NoopWorker extends Worker
{
    protected ?Kernel $kernel;

    public function __construct(?Kernel $kernel = null)
    {
        $this->kernel = $kernel;
        parent::__construct(static fn () => null, new Timers());
    }
}
