<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker\Queue;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Bus\Queue\Consumer as QueueConsumer;
use Nayleen\Async\Bus\Queue\Queue;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class ConsumerTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_run(): void
    {
        $kernel = TestKernel::create();
        $queue = $this->createStub(Queue::class);

        $queueConsumer = $this->createMock(QueueConsumer::class);
        $queueConsumer->expects(self::once())->method('consume')->with($queue);

        $worker = new Consumer($queueConsumer, $queue);
        $worker->run($kernel);
    }
}
