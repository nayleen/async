<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Nayleen\Async\Kernel;
use Nayleen\Async\Task;
use Nayleen\Async\Timers;

abstract class Worker extends Task
{
    public function __construct(public Timers $timers = new Timers()) {}

    protected function execute(Kernel $kernel): null
    {
        try {
            $this->timers->start($kernel);
            $kernel->trap(...$this->signals());

            return null;
        } finally {
            $this->timers->stop();
        }
    }

    /**
     * @return iterable<int>
     */
    protected function signals(): iterable
    {
        return [SIGINT, SIGQUIT, SIGTERM];
    }
}
