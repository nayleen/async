<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Task as TaskInterface;
use Closure;

readonly class Worker extends Runtime implements TaskInterface
{
    public function __construct(
        Closure $closure,
        public Timers $timers = new Timers(),
    ) {
        parent::__construct($closure);
    }

    /**
     * @return iterable<int>
     */
    protected function signals(): iterable
    {
        return [SIGINT, SIGQUIT, SIGTERM];
    }

    public function execute(Kernel $kernel): null
    {
        try {
            $this->timers->start($kernel);
            parent::execute($kernel);

            $kernel->trap(...$this->signals());

            return null;
        } finally {
            $this->timers->stop();
        }
    }
}
