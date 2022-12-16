<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Command;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Generator;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;
use OutOfBoundsException;
use RuntimeException;

/**
 * @internal
 */
class PublishesUnhandledMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function does_not_publish_on_success(): Generator
    {
        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::never())->method('publish');

        $middleware = new PublishesUnhandledMiddleware($publisher, $this->createMock(Queue::class));
        yield $middleware->handle(
            $this->createMock(Message::class),
            $this->createCallback(1, fn (Message $message) => new Success()),
        );
    }

    /**
     * @test
     */
    public function publishes_on_missing_handler(): Generator
    {
        $message = $this->createMock(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message)->willReturn(new Success());

        $middleware = new PublishesUnhandledMiddleware($publisher, $queue);
        yield $middleware->handle(
            $message,
            $this->createCallback(1, fn (Message $message) => throw new OutOfBoundsException()),
        );
    }

    /**
     * @test
     */
    public function publishes_only_on_missing_handler(): Generator
    {
        $this->expectException(RuntimeException::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::never())->method('publish');

        $middleware = new PublishesUnhandledMiddleware($publisher, $this->createMock(Queue::class));
        yield $middleware->handle(
            $this->createMock(Message::class),
            $this->createCallback(1, fn (Message $message) => throw new RuntimeException()),
        );
    }
}
