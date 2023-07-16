<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Bus\Queue\Redis\Connection;

/**
 * @internal
 */
final class RedisQueueTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_consume_queue(): void
    {
        $list = 'test';
        $expectedMessage = 'message';

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('popListHead')->with($list)->willReturn($expectedMessage);

        $queue = new RedisQueue($connection, $list);
        $message = $queue->consume();

        self::assertSame($expectedMessage, $message);
    }

    /**
     * @test
     */
    public function can_enqueue(): void
    {
        $list = 'test';
        $message = 'message';

        $connection = $this->createMock(Connection::class);
        $connection->expects(self::once())->method('pushListTail')->with($list, $message);

        $queue = new RedisQueue($connection, $list);
        $queue->enqueue($message);
    }

    /**
     * @test
     */
    public function returns_name(): void
    {
        $queue = new RedisQueue($this->createMock(Connection::class), 'test');
        self::assertSame('test', $queue->name());
    }
}
