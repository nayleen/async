<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Task\Worker;
use Nayleen\Async\Timers;

final class NoopWorker extends Worker
{
    public function __construct()
    {
        parent::__construct(new Timers());
    }
}
