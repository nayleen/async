<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker;

use Nayleen\Async\Timer\Timers;
use Revolt\EventLoop\Driver;

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

    abstract public function run(): void;
}
