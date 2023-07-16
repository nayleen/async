<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;

/**
 * @internal
 */
final class AlwaysPublishesMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function always_publishes(): void
    {
        $message = $this->createStub(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message);

        $middleware = new AlwaysPublishesMiddleware($publisher, $queue);
        $middleware->handle($message, $this->createCallback(1, fn (Message $message) => null));
    }
}
