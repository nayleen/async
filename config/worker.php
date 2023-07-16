<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Context\ContextFactory as ContextFactoryInterface;
use Amp\Parallel\Context\DefaultContextFactory;
use Amp\Parallel\Worker\ContextWorkerFactory;
use Amp\Parallel\Worker\ContextWorkerPool;
use Amp\Parallel\Worker\WorkerFactory;
use Amp\Parallel\Worker\WorkerPool;
use DI;
use Nayleen\Async\Task\ContextFactory;

return [
    // worker config
    'async.worker.bootstrap_path' => null,
    'async.worker_pool.size' => WorkerPool::DEFAULT_WORKER_LIMIT,

    // worker services
    ContextFactoryInterface::class => static function (DI\Container $container): ContextFactoryInterface {
        return new ContextFactory(
            new DefaultContextFactory(),
            $container->get('async.stdout'),
            $container->get('async.stderr'),
        );
    },

    WorkerFactory::class => static function (DI\Container $container): WorkerFactory {
        return new ContextWorkerFactory(
            $container->get('async.worker.bootstrap_path'),
            $container->get(ContextFactoryInterface::class),
        );
    },

    WorkerPool::class => static function (DI\Container $container): WorkerPool {
        return new ContextWorkerPool(
            $container->get('async.worker_pool.size'),
            $container->get(WorkerFactory::class),
        );
    },
];
