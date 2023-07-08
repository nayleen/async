<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cancellation;

/**
 * @api
 */
abstract class Worker
{
    public function __construct(private readonly Timers $timers = new Timers())
    {
    }

    abstract protected function execute(Cancellation $cancellation): void;

    protected function start(Kernel $kernel): void
    {
        $kernel->cancellation()->subscribe($this->stop(...));

        $this->timers->start($kernel);
    }

    public function run(Kernel $kernel): void
    {
        $this->start($kernel);
        $this->execute($kernel->cancellation());
    }

    public function stop(): void
    {
        $this->timers->stop();
    }
}
