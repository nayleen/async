<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Redis;
use DI;
use RuntimeException;

return [
    // redis config
    'async.redis.available' => DI\factory(static fn (): bool => class_exists(Redis\RedisClient::class)),

    'async.redis.dsn' => DI\factory(static function (bool $redisEnabled, ?string $dsn): string {
        if (!$redisEnabled) {
            throw new RuntimeException('Redis support is not installed or you have not configured the ASYNC_REDIS_DSN environment variable.');
        }

        assert($dsn !== null);

        return $dsn;
    })
        ->parameter('redisEnabled', DI\get('async.redis.enabled'))
        ->parameter('dsn', DI\env('ASYNC_REDIS_DSN', null)),

    'async.redis.enabled' => DI\factory(static fn (bool $redisAvailable, ?string $dsn): bool => $redisAvailable && $dsn !== null)
        ->parameter('redisAvailable', DI\get('async.redis.available'))
        ->parameter('dsn', DI\env('ASYNC_REDIS_DSN', null)),

    // redis services
    Redis\RedisClient::class => DI\factory(static fn (string $dsn): Redis\RedisClient => Redis\createRedisClient($dsn))
        ->parameter('dsn', DI\get('async.redis.dsn')),
];
