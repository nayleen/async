<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Queue;

use Amp\NullCancellation;
use Nayleen\Async\Bus\Queue\Consumer as QueueConsumer;
use Nayleen\Async\Bus\Queue\Queue;
use Nayleen\Async\Kernel;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ConsumerTest extends TestCase
{
    /**
     * @test
     */
    public function can_run(): void
    {
        $cancellation = new NullCancellation();

        $kernel = $this->createMock(Kernel::class);
        $kernel->method('cancellation')->willReturn($cancellation);

        $queue = $this->createStub(Queue::class);

        $queueConsumer = $this->createMock(QueueConsumer::class);
        $queueConsumer->expects(self::once())->method('consume')->with($queue, $cancellation);

        $worker = new Consumer($queueConsumer, $queue);
        $worker->run($kernel);
    }
}
