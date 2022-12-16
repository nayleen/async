<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Redis\Redis;
use Amp\Redis\RedisList;
use Amp\Success;
use Generator;

/**
 * @internal
 */
class RedisQueueTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_consume_queue(): Generator
    {
        $list = $this->createMock(RedisList::class);
        $list->expects(self::once())->method('popHead')->willReturn(new Success('message'));

        $redis = $this->createMock(Redis::class);
        $redis->expects(self::once())->method('getList')->willReturn($list);

        $queue = new RedisQueue($redis, 'test');
        $message = yield $queue->consume();

        self::assertSame('message', $message);
    }

    /**
     * @test
     */
    public function can_enqueue(): Generator
    {
        $list = $this->createMock(RedisList::class);
        $list->expects(self::once())->method('pushTail')->with('message')->willReturn(new Success());

        $redis = $this->createMock(Redis::class);
        $redis->expects(self::once())->method('getList')->willReturn($list);

        $queue = new RedisQueue($redis, 'test');
        yield $queue->enqueue('message');
    }

    /**
     * @test
     */
    public function returns_name(): void
    {
        $queue = new RedisQueue($this->createMock(Redis::class), 'test');
        self::assertSame('test', $queue->name());
    }
}
