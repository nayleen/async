<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\PHPUnit\AsyncTestCase;
use Generator;

/**
 * @internal
 */
class InMemoryQueueTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_consume_queue(): Generator
    {
        $queue = new InMemoryQueue('test');

        yield $queue->enqueue('message');
        $message = yield $queue->consume();

        self::assertSame('message', $message);
    }

    /**
     * @test
     */
    public function returns_name(): void
    {
        $queue = new InMemoryQueue('test');
        self::assertSame('test', $queue->name());
    }
}
