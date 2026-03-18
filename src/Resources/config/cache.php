<?php

declare(strict_types = 1);

use Amp\Cache\AtomicCache;
use Amp\Cache\Cache;
use Amp\Cache\LocalCache;
use Amp\Redis\RedisCache;
use Amp\Redis\Sync\RedisMutex;
use Amp\Sync\KeyedMutex;
use Amp\Sync\LocalKeyedMutex;
use Amp\Sync\LocalMutex;
use Amp\Sync\Mutex;
use Psr\Container\ContainerInterface;

return [
    // parameters

    // services
    AtomicCache::class => DI\factory(static fn (ContainerInterface $c) => new AtomicCache(
        cache: $c->get(Cache::class),
        mutex: $c->get(KeyedMutex::class),
    )),

    Cache::class => DI\factory(static function (ContainerInterface $c, bool $redisEnabled) {
        if ($redisEnabled) {
            return $c->get(RedisCache::class);
        }

        return new LocalCache();
    })->parameter('redisEnabled', DI\get('redis.enabled')),

    KeyedMutex::class => DI\factory(static function (ContainerInterface $c, bool $redisEnabled) {
        if ($redisEnabled) {
            return $c->get(RedisMutex::class);
        }

        return new LocalKeyedMutex();
    })->parameter('redisEnabled', DI\get('redis.enabled')),

    Mutex::class => DI\factory(static fn () => new LocalMutex()),
];
