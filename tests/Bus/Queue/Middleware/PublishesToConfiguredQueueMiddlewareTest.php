<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Generator;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;
use Nayleen\Async\Bus\Queue\QueueMap;
use OutOfBoundsException;

/**
 * @internal
 */
class PublishesToConfiguredQueueMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function not_mapped_exception_rethrown_without_fallback(): Generator
    {
        $this->expectException(OutOfBoundsException::class);

        $message = $this->createMock(Message::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::never())->method('publish');

        $middleware = new PublishesToConfiguredQueueMiddleware($publisher, new QueueMap());
        yield $middleware->handle($message, $this->createCallback(0, fn (Message $message) => new Success()));
    }

    /**
     * @test
     */
    public function publishes_to_fallback_queue_if_unmapped(): Generator
    {
        $message = $this->createMock(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message)->willReturn(new Success());

        $middleware = new PublishesToConfiguredQueueMiddleware($publisher, new QueueMap(), $queue);
        yield $middleware->handle($message, $this->createCallback(1, fn (Message $message) => new Success()));
    }

    /**
     * @test
     */
    public function publishes_to_mapped_queue(): Generator
    {
        $name = 'test';

        $message = $this->createMock(Message::class);
        $message->expects(self::once())->method('name')->willReturn($name);

        $fallback = $this->createMock(Queue::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message)->willReturn(new Success());

        $map = new QueueMap([$name => $queue]);

        $middleware = new PublishesToConfiguredQueueMiddleware($publisher, $map, $fallback);
        yield $middleware->handle($message, $this->createCallback(1, fn (Message $message) => new Success()));
    }
}
