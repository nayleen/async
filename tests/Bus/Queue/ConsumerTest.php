<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\DeferredCancellation;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Serialization\Serializer;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;

/**
 * @internal
 */
class ConsumerTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function consumes_queue_and_forwards_to_bus(): void
    {
        $message = $this->createMock(Message::class);
        $encoded = 'message';

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('unserialize')->with($encoded)->willReturn($message);

        $bus = $this->createMock(Bus::class);
        $bus->expects(self::once())->method('handle')->with($message);

        $cancellation = new DeferredCancellation();

        $queue = $this->createMock(Queue::class);
        $queue->expects(self::exactly(3))->method('consume')->willReturnOnConsecutiveCalls(
            $encoded,
            null,
            self::returnCallback(static fn () => $cancellation->cancel()),
        );

        $consumer = new Consumer($bus, $serializer, 0);
        $consumer->consume($queue, $cancellation->getCancellation());
    }
}
