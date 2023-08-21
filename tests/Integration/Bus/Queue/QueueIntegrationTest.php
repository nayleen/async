<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\DeferredCancellation;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Serialization\Serializer;
use Generator;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Queue\Redis\Connection;

use function Amp\Redis\createRedisClient;

/**
 * @internal
 */
final class QueueIntegrationTest extends AsyncTestCase
{
    /**
     * @test
     * @dataProvider queueImplementations
     */
    public function can_consume_queue_and_forward_to_bus(Queue $queue): void
    {
        $message = $this->createMock(Message::class);
        $encoded = 'message';

        $serializer = $this->createConfiguredMock(Serializer::class, [
            'unserialize' => $message,
        ]);

        $cancellation = new DeferredCancellation();

        $bus = $this->createMock(Bus::class);
        $bus->expects(self::once())->method('handle')->with($message)->willReturnCallback(
            function () use ($cancellation): void {
                $cancellation->cancel();
            },
        );

        $queue->enqueue($encoded);

        $consumer = new Consumer($bus, $serializer, 0);
        $consumer->consume($queue, $cancellation->getCancellation());
    }

    /**
     * @test
     * @dataProvider queueImplementations
     */
    public function can_publish(Queue $queue): void
    {
        $message = $this->createMock(Message::class);
        $encoded = 'message';

        $serializer = $this->createConfiguredMock(Serializer::class, [
            'serialize' => $encoded,
        ]);

        $publisher = new Publisher($serializer);
        $publisher->publish($queue, $message);

        self::assertSame($encoded, $queue->consume());
    }

    /**
     * @test
     * @dataProvider queueImplementations
     */
    public function consume_returns_null_on_empty_queue(Queue $queue): void
    {
        $return = $queue->consume();

        self::assertNull($return);
    }

    public function queueImplementations(): Generator
    {
        yield 'in_memory' => [new InMemoryQueue('test-queue')];
        yield 'redis' => [
            new RedisQueue(
                new Connection(createRedisClient('redis://redis')),
                'test-queue',
            ),
        ];
    }
}
