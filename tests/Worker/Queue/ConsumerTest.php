<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Queue;

use Amp\NullCancellation;
use Nayleen\Async\Bus\Queue\Consumer as QueueConsumer;
use Nayleen\Async\Bus\Queue\Queue;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ConsumerTest extends TestCase
{
    /**
     * @test
     */
    public function can_run(): void
    {
        $cancellation = new NullCancellation();
        $queue = $this->createStub(Queue::class);

        $queueConsumer = $this->createMock(QueueConsumer::class);
        $queueConsumer->expects(self::once())->method('consume')->with($queue, $cancellation);

        $worker = new Consumer($queueConsumer, $queue);
        $worker->run($cancellation);
    }
}
