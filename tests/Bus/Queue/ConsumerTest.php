<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\CancellationTokenSource;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Serialization\Serializer;
use Amp\Success;
use Generator;
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
    public function consumes_queue_and_forwards_to_bus(): Generator
    {
        $message = $this->createMock(Message::class);
        $encoded = 'message';

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('unserialize')->with($encoded)->willReturn($message);

        $bus = $this->createMock(Bus::class);
        $bus->expects(self::once())->method('handle')->with($message)->willReturn(new Success());

        $cancellation = new CancellationTokenSource();

        $queue = $this->createMock(Queue::class);
        $queue->expects(self::exactly(2))->method('consume')->willReturnOnConsecutiveCalls(
            new Success($encoded),
            self::returnCallback(function () use ($cancellation) {
                $cancellation->cancel();

                return new Success(null);
            }),
        );

        $consumer = new Consumer($bus, $serializer, 0);
        yield $consumer->consume($queue, $cancellation->getToken());
    }
}
