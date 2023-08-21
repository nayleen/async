<?php

declare(strict_types = 1);

namespace Nayleen\Async\Bus\Queue\Redis;

use Amp\PHPUnit\AsyncTestCase;

use function Amp\Redis\createRedisClient;

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

        $connection = new Connection(createRedisClient('redis://redis'));
        $connection->pushListTail($list, $expectedMessage);

        $message = $connection->popListHead($list);
        self::assertSame($expectedMessage, $message);
    }
}
