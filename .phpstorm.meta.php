<?php

namespace PHPSTORM_META
{
    override(
        \DI\Container::get(0),
        map([
            '' => '@',
            'async.debug' => \bool,
            'async.env' => \string,
            'async.logger.stderr' => \Psr\Log\LoggerInterface::class,
            'async.logger.stdout' => \Psr\Log\LoggerInterface::class,
            'async.worker_pool' => \Amp\Parallel\Worker\WorkerPool::class,
        ])
    );

    override(
        \DI\Container::make(0),
        map([
            '' => '@',
        ])
    );

    override(
        \Nayleen\Async\Kernel::make(0),
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
