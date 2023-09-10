<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Context\ContextFactory as ContextFactoryInterface;
use Amp\Parallel\Ipc\IpcHub;
use Amp\Parallel\Ipc\LocalIpcHub;
use Amp\Parallel\Worker\ContextWorkerFactory;
use Amp\Parallel\Worker\ContextWorkerPool;
use Amp\Parallel\Worker\WorkerFactory;
use Amp\Parallel\Worker\WorkerPool;
use DI;
use Nayleen\Async\Task\ContextFactory;
use Safe;

return [
    // worker config
    'async.worker.bootstrap_path' => null,
    'async.worker.context' => static fn () => defined('\AMP_CONTEXT') ? \AMP_CONTEXT : 'main',
    'async.worker.context_id' => static fn () => defined('\AMP_CONTEXT_ID') ? \AMP_CONTEXT_ID : Safe\getmypid(),
    'async.worker.pool_size' => WorkerPool::DEFAULT_WORKER_LIMIT,

    // worker services
    ContextFactoryInterface::class => static function (DI\Container $container): ContextFactoryInterface {
        return new ContextFactory(
            $container->get('async.stdout'),
            $container->get('async.stderr'),
            $container->get(IpcHub::class),
        );
    },

    IpcHub::class => DI\autowire(LocalIpcHub::class),

    WorkerFactory::class => static function (DI\Container $container): WorkerFactory {
        return new ContextWorkerFactory(
            $container->get('async.worker.bootstrap_path'),
            $container->get(ContextFactoryInterface::class),
        );
    },

    WorkerPool::class => static function (DI\Container $container): WorkerPool {
        return new ContextWorkerPool(
            $container->get('async.worker.pool_size'),
            $container->get(WorkerFactory::class),
        );
    },
];
