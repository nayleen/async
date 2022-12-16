<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Exception;
use Generator;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;

/**
 * @internal
 */
class PublishesOnErrorMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function only_publishes_on_error(): Generator
    {
        $message = $this->createStub(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::never())->method('publish')->with($queue, $message)->willReturn(new Success());

        $middleware = new PublishesOnErrorMiddleware($publisher, $queue);
        yield $middleware->handle($message, $this->createCallback(1, fn (Message $message) => new Success()));
    }

    /**
     * @test
     */
    public function publishes_on_error(): Generator
    {
        $this->expectException(Exception::class);

        $message = $this->createStub(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message)->willReturn(new Success());

        $middleware = new PublishesOnErrorMiddleware($publisher, $queue);
        yield $middleware->handle($message, $this->createCallback(1, fn (Message $message) => throw new Exception()));
    }

    /**
     * @test
     */
    public function publishes_on_error_and_continues_if_configured(): Generator
    {
        $message = $this->createStub(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message)->willReturn(new Success());

        $middleware = new PublishesOnErrorMiddleware($publisher, $queue, false);
        yield $middleware->handle($message, $this->createCallback(1, fn (Message $message) => throw new Exception()));
    }
}
