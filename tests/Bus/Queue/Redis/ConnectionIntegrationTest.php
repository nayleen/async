<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Redis;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Redis\Redis;
use Amp\Redis\RedisConfig;
use Amp\Redis\RemoteExecutor;

/**
 * @internal
 */
final class ConnectionIntegrationTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_work_with_lists(): void
    {
        $list = 'test';
        $expectedMessage = 'message';

        $redis = new Redis(new RemoteExecutor(RedisConfig::fromUri('redis://redis')));

        $connection = new Connection($redis);
        $connection->pushListTail($list, $expectedMessage);

        $message = $connection->popListHead($list);
        self::assertSame($expectedMessage, $message);
    }
}
