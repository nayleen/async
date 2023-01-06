<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Queue;

use Nayleen\Async\Bus\Queue\Consumer as QueueConsumer;
use Nayleen\Async\Bus\Queue\Queue;
use Nayleen\Async\Worker\Worker;

final class Consumer extends Worker
{
    public function __construct(
        private readonly QueueConsumer $consumer,
        private readonly Queue $queue,
    ) {
    }

    public function run(): void
    {
        $this->consumer->consume($this->queue);
    }
}
