<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cache\AtomicCache;
use Amp\Cache\Cache;
use Amp\Cache\LocalCache;
use Amp\Redis\QueryExecutor;
use Amp\Redis\Redis;
use Amp\Redis\RedisCache;
use Amp\Redis\Sync\RedisMutex;
use Amp\Redis\Sync\RedisMutexOptions;
use Amp\Serialization\Serializer;
use Amp\Sync\KeyedMutex;
use Amp\Sync\LocalKeyedMutex;
use DI;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    // cache parameters
    'async.cache.redis' => DI\get('async.redis_support'),

    // cache services
    AtomicCache::class => DI\autowire(),

    Cache::class => DI\factory(static function (
        bool $redisEnabled,
        ContainerInterface $container,
    ): Cache {
        if (!$redisEnabled) {
            return new LocalCache();
        }

        return new RedisCache(
            $container->get(Redis::class),
            $container->get(Serializer::class),
        );
    })
        ->parameter('redisEnabled', DI\get('async.cache.redis')),

    KeyedMutex::class => DI\factory(static function (
        bool $redisEnabled,
        ContainerInterface $container,
    ): KeyedMutex {
        if (!$redisEnabled) {
            return new LocalKeyedMutex();
        }

        return new RedisMutex(
            $container->get(QueryExecutor::class),
            $container->get(RedisMutexOptions::class),
            $container->get(LoggerInterface::class),
        );
    })
        ->parameter('redisEnabled', DI\get('async.cache.redis')),
];
