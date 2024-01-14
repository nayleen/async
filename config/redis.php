<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Redis;
use DI;
use RuntimeException;

return [
    // redis config
    'async.redis_dsn' => DI\factory(static function (?string $dsn, bool $redisSupported): string {
        if (!$redisSupported) {
            throw new RuntimeException();
        }

        if ($dsn === null) {
            throw new RuntimeException();
        }

        return $dsn;
    })
        ->parameter('dsn', DI\env('ASYNC_REDIS_DSN', null))
        ->parameter('redisSupported', DI\get('async.redis_support')),

    'async.redis_support' => DI\factory(static fn (): bool => class_exists(Redis\RedisClient::class)),

    // redis services
    Redis\RedisClient::class => DI\factory(static fn (string $dsn): Redis\RedisClient => Redis\createRedisClient($dsn))
        ->parameter('dsn', DI\get('async.redis_dsn')),
];
