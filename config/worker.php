<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\ContextWorkerFactory;
use Amp\Parallel\Worker\ContextWorkerPool;
use Amp\Parallel\Worker\WorkerFactory;
use Amp\Parallel\Worker\WorkerPool;
use DI;

return [
    // worker config
    'async.worker_pool.size' => WorkerPool::DEFAULT_WORKER_LIMIT,

    // worker services
    WorkerFactory::class => static fn (): WorkerFactory => new ContextWorkerFactory(),

    WorkerPool::class => DI\factory(static fn (int $limit): WorkerPool => new ContextWorkerPool($limit))
        ->parameter('limit', DI\get('async.worker_pool.size')),
];
