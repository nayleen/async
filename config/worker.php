<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cluster\ClusterWatcher;
use Amp\Cluster\ServerSocketPipeProvider;
use Amp\Parallel\Context\ContextFactory as ContextFactoryInterface;
use Amp\Parallel\Context\ProcessContextFactory;
use Amp\Parallel\Ipc\IpcHub;
use Amp\Parallel\Ipc\LocalIpcHub;
use Amp\Parallel\Worker\ContextWorkerFactory;
use Amp\Parallel\Worker\ContextWorkerPool;
use Amp\Parallel\Worker\WorkerFactory;
use Amp\Parallel\Worker\WorkerPool;
use DI;
use Monolog\Level;
use Monolog\Logger;
use Nayleen\Async\Task\ContextFactory;
use Psr\Log\LogLevel;
use Safe;

return [
    // worker config
    'async.worker' => DI\factory(static fn (string $context): bool => $context !== 'main')
        ->parameter('context', DI\get('async.worker.context')),

    'async.worker.bootstrap_path' => null,
    'async.worker.context' => static fn () => defined('\AMP_CONTEXT') ? \AMP_CONTEXT : 'main',
    'async.worker.context_id' => static fn () => defined('\AMP_CONTEXT_ID') ? \AMP_CONTEXT_ID : Safe\getmypid(),
    'async.worker.log_threshold' => DI\factory(static fn (bool $debug): int|Level|string => $debug ? LogLevel::DEBUG : LogLevel::WARNING)
        ->parameter('debug', DI\get('async.debug')),

    'async.worker.pool_size' => WorkerPool::DEFAULT_WORKER_LIMIT,

    // worker services
    ClusterWatcher::class => DI\factory(static function (
        string $script,
        ?Logger $logger,
        DI\Container $container,
    ): ClusterWatcher {
        return new ClusterWatcher(
            $script,
            $logger ?? $container->get(Logger::class),
            $container->get(IpcHub::class),
            $container->get(ContextFactoryInterface::class),
            $container->get(ServerSocketPipeProvider::class),
        );
    }),

    ContextFactoryInterface::class => static function (DI\Container $container): ContextFactoryInterface {
        return new ContextFactory(
            $container->get('async.stdout'),
            $container->get('async.stderr'),
            new ProcessContextFactory(ipcHub: $container->get(IpcHub::class)),
        );
    },

    IpcHub::class => DI\get(LocalIpcHub::class),
    LocalIpcHub::class => DI\autowire(LocalIpcHub::class),

    ServerSocketPipeProvider::class => static fn (): ServerSocketPipeProvider => new ServerSocketPipeProvider(),

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
