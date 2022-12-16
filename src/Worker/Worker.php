<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker;

use Amp\Loop\Driver;
use Amp\Promise;
use Nayleen\Async\Timer\Timers;

abstract class Worker
{
    public function setup(Driver $loop): void
    {
        $this->timers()->setup($loop);
    }

    protected function timers(): Timers
    {
        return new Timers();
    }

    abstract public function run(): Promise;
}
