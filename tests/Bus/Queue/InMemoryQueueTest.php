<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\PHPUnit\AsyncTestCase;

/**
 * @internal
 */
final class InMemoryQueueTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_consume_queue(): void
    {
        $queue = new InMemoryQueue('test');

        $queue->enqueue('message');
        $message = $queue->consume();

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
