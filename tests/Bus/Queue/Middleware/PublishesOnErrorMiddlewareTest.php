<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Exception;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;

/**
 * @internal
 */
final class PublishesOnErrorMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function only_publishes_on_error(): void
    {
        $message = $this->createStub(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::never())->method('publish')->with($queue, $message);

        $middleware = new PublishesOnErrorMiddleware($publisher, $queue);
        $middleware->handle($message, $this->createCallback(1, fn (Message $message) => null));
    }

    /**
     * @test
     */
    public function publishes_on_error(): void
    {
        $this->expectException(Exception::class);

        $message = $this->createStub(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message);

        $middleware = new PublishesOnErrorMiddleware($publisher, $queue);
        $middleware->handle($message, $this->createCallback(1, fn (Message $message) => throw new Exception()));
    }

    /**
     * @test
     */
    public function publishes_on_error_and_continues_if_configured(): void
    {
        $message = $this->createStub(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message);

        $middleware = new PublishesOnErrorMiddleware($publisher, $queue, false);
        $middleware->handle($message, $this->createCallback(1, fn (Message $message) => throw new Exception()));
    }
}
