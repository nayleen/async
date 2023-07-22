<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Queue;

use Amp\Cancellation;
use Nayleen\Async\Bus\Queue\Consumer as QueueConsumer;
use Nayleen\Async\Bus\Queue\Queue;
use Nayleen\Async\Timers;
use Nayleen\Async\Worker;

class Consumer extends Worker
{
    public function __construct(
        private readonly QueueConsumer $consumer,
        private readonly Queue $queue,
        Timers $timers = new Timers(),
    ) {
        parent::__construct($timers);
    }

    protected function execute(Cancellation $cancellation): void
    {
        $this->consumer->consume($this->queue, $cancellation);
    }
}
