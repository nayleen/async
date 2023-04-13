<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Redis\QueryExecutor;
use Amp\Redis\QueryExecutorFactory;
use Amp\Redis\Redis;
use Amp\Redis\RedisConfig;
use Amp\Redis\RemoteExecutorFactory;
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
        ->parameter('redisSupported', DI\get('async.redis_supported')),

    'async.redis_supported' => DI\factory(static fn (): bool => class_exists(Redis::class)),

    // redis services
    QueryExecutor::class => DI\factory(static fn (QueryExecutorFactory $executorFactory): QueryExecutor => $executorFactory->createQueryExecutor()),

    QueryExecutorFactory::class => DI\factory(static fn (RedisConfig $config): QueryExecutorFactory => new RemoteExecutorFactory($config)),

    Redis::class => DI\factory(static fn (QueryExecutor $executor): Redis => new Redis($executor)),

    RedisConfig::class => DI\factory(static fn (string $dsn): RedisConfig => RedisConfig::fromUri($dsn))
        ->parameter('dsn', DI\get('async.redis_dsn')),
];
