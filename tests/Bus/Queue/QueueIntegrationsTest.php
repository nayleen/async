<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\DeferredCancellation;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Redis\RedisConfig;
use Amp\Redis\Redis;
use Amp\Redis\RemoteExecutor;
use Amp\Serialization\Serializer;
use Generator;
use Nayleen\Async\Bus\Bus;
use Nayleen\Async\Bus\Message;

/**
 * @internal
 */
class QueueIntegrationsTest extends AsyncTestCase
{
    /**
     * @test
     * @dataProvider queueImplementations
     */
    public function can_consume_queue_and_forward_to_bus(Queue $queue): void
    {
        $message = $this->createMock(Message::class);
        $encoded = 'message';

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects(self::once())->method('unserialize')->with($encoded)->willReturn($message);

        $cancellation = new DeferredCancellation();

        $bus = $this->createMock(Bus::class);
        $bus->expects(self::once())->method('handle')->with($message)->willReturnCallback(function () use ($cancellation) {
            $cancellation->cancel();
        });

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

        $serializer = $this->createMock(Serializer::class);
        $serializer->method('serialize')->with($message)->willReturn($encoded);

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
        yield 'redis' => [new RedisQueue(new Redis(new RemoteExecutor(RedisConfig::fromUri('redis://redis'))), 'test-queue')];
    }
}
