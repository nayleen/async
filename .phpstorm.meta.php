<?php

namespace PHPSTORM_META
{
    override(
        \DI\Container::get(0),
        map([
            '' => '@',
            // config/async.php
            'async.app_name' => \string,
            'async.app_version' => \string,
            'async.debug' => \bool,
            'async.dir.base' => \string,
            'async.dir.cache' => \string,
            'async.dir.tmp' => \string,
            'async.env' => \string,
            'async.exceptions.handler' => \Closure::class,
            'async.advisories' => \bool,
            'async.stderr' => \Amp\ByteStream\WritableResourceStream::class,
            'async.stdin' => \Amp\ByteStream\ReadableResourceStream::class,
            'async.stdout' => \Amp\ByteStream\WritableResourceStream::class,
            // config/cache.php
            'async.cache.redis' => \bool,
            // config/logging.php
            'async.logger.date_format' => \string,
            'async.logger.format' => \string,
            'async.logger.level' => \int|\string|\Monolog\Level::class,
            'async.logger.name' => \string,
            // config/redis.php
            'async.redis.available' => \bool,
            'async.redis.dsn' => \string,
            'async.redis.enabled' => \bool,
            // config/worker.php
            'async.worker' => \bool,
            'async.worker.bootstrap_path' => \null|\string,
            'async.worker.context' => \string,
            'async.worker.context_id' => \int,
            'async.worker.log_threshold' => \int|\string|\Monolog\Level::class,
            'async.worker_pool.size' => \int,
        ])
    );

    override(
        \DI\Container::make(0),
        map([
            '' => '@',
        ])
    );

    override(
        \Psr\Container\ContainerInterface::get(0),
        map([
            '' => '@',
        ])
    );
}
