<?php

namespace PHPSTORM_META
{
    override(
        \DI\Container::get(0),
        map([
            '' => '@',
            'async.app_name' => \string,
            'async.app_version' => \string,
            'async.cache.redis' => \bool,
            'async.compile_container' => \bool,
            'async.debug' => \bool,
            'async.dir.base' => \string,
            'async.dir.cache' => \string,
            'async.dir.tmp' => \string,
            'async.env' => \string,
            'async.exception_handler' => \callable,
            'async.logger.date_format' => \string,
            'async.logger.format' => \string,
            'async.logger.level' => \int|\string|\Monolog\Level::class,
            'async.redis_supported' => \bool,
            'async.worker_pool.size' => \int,
            'async.logger.debug' => \Psr\Log\LoggerInterface::class,
            'async.logger' => \Psr\Log\LoggerInterface::class,
            'async.stderr' => \Amp\ByteStream\WritableResourceStream::class,
            'async.stdin' => \Amp\ByteStream\ReadableResourceStream::class,
            'async.stdout' => \Amp\ByteStream\WritableResourceStream::class,
        ])
    );

    override(
        \Psr\Container\ContainerInterface::get(0),
        map([
            '' => '@',
        ])
    );
}
