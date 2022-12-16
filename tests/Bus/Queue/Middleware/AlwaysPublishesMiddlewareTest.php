<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Middleware;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Generator;
use Nayleen\Async\Bus\Message;
use Nayleen\Async\Bus\Queue\Publisher;
use Nayleen\Async\Bus\Queue\Queue;

/**
 * @internal
 */
class AlwaysPublishesMiddlewareTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function always_publishes(): Generator
    {
        $message = $this->createStub(Message::class);
        $queue = $this->createMock(Queue::class);

        $publisher = $this->createMock(Publisher::class);
        $publisher->expects(self::once())->method('publish')->with($queue, $message)->willReturn(new Success());

        $middleware = new AlwaysPublishesMiddleware($publisher, $queue);
        yield $middleware->handle($message, $this->createCallback(1, fn (Message $message) => new Success()));
    }
}
