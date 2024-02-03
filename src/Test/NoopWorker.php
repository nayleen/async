<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Timers;
use Nayleen\Async\Worker;

final readonly class NoopWorker extends Worker
{
    public function __construct()
    {
        parent::__construct(static fn () => null, new Timers());
    }
}
