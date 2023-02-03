<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream;
use Amp\Cluster\Cluster;
use Amp\Parallel\Worker\DefaultWorkerPool;
use Amp\Parallel\Worker\WorkerPool;
use Amp\Serialization\NativeSerializer;
use Amp\Serialization\Serializer;
use Amp\Socket\ResourceSocketServerFactory;
use Amp\Socket\SocketServerFactory;
use Amp\Sync\Channel;
use Closure;
use DI;
use Monolog\ErrorHandler;
use Nayleen\Async\Worker\ContextFactory;
use Nayleen\Async\Worker\WorkerFactory;
use Psr\Container\ContainerInterface;
use Revolt\EventLoop;
use Throwable;

return [
    // app config
    'async.app_name' => DI\env('ASYNC_APP_NAME', 'Kernel'),
    'async.debug' => DI\factory(static function (string $env): bool {
        return $env !== 'prod';
    })
        ->parameter('env', DI\get('async.env')),
    'async.env' => strtolower((getenv('ASYNC_ENV') ?: 'prod')),
    'async.worker_pool.size' => WorkerPool::DEFAULT_WORKER_LIMIT,

    // cluster config
    'async.cluster.enabled' => DI\factory(static function (string $envValue): bool {
        if (!class_exists(Cluster::class)) {
            return false;
        }

        return (bool) $envValue;
    })
        ->parameter('envValue', DI\env('ASYNC_CLUSTER', 'true')),

    'async.cluster.is_worker' => static function (ContainerInterface $container): bool {
        $clusterSupport = (bool) $container->get('async.cluster.enabled');

        if (!$clusterSupport) {
            return false;
        }

        return Cluster::isWorker();
    },

    // directories
    'async.dir.base' => DI\env('ASYNC_DIR'),
    'async.dir.cache' => static fn (): string => sys_get_temp_dir(),

    // app services
    'async.exception_handler' => static function (ErrorHandler $errorHandler): callable {
        $errorHandler->registerExceptionHandler();
        $exceptionHandler = set_exception_handler(null);
        assert(is_callable($exceptionHandler));

        set_exception_handler($exceptionHandler);

        return $exceptionHandler;
    },

    'async.stderr' => static fn (): ByteStream\WritableResourceStream => ByteStream\getStderr(),
    'async.stdin' => static fn (): ByteStream\ReadableResourceStream => ByteStream\getStdin(),
    'async.stdout' => static fn (): ByteStream\WritableResourceStream => ByteStream\getStdout(),

    Channel::class => DI\decorate(static function (
        ?Channel $channel,
        ContainerInterface $container,
    ): Channel {
        return $channel ?? new ByteStream\StreamChannel(
            $container->get('async.stdin'),
            $container->get('async.stdout'),
        );
    }),

    ContextFactory::class => DI\autowire(),

    EventLoop\Driver::class => DI\factory(static function (
        Closure $errorHandler,
        bool $debug,
    ): EventLoop\Driver {
        $driver = (new EventLoop\DriverFactory())->create();

        if (!($driver instanceof EventLoop\Driver\TracingDriver) && $debug) {
            $driver = new EventLoop\Driver\TracingDriver($driver);
        }

        /**
         * @var Closure(Throwable): void $errorHandler
         */
        $driver->setErrorHandler($errorHandler);

        return $driver;
    })
        ->parameter('errorHandler', DI\get('async.exception_handler'))
        ->parameter('debug', DI\get('async.debug')),

    Serializer::class => static fn (): Serializer => new NativeSerializer(),

    SocketServerFactory::class => DI\factory(static function (bool $clusterSupport): SocketServerFactory {
        return $clusterSupport
            ? Cluster::getSocketServerFactory()
            : new ResourceSocketServerFactory();
    })
        ->parameter('clusterSupport', DI\get('async.cluster.enabled')),

    WorkerFactory::class => static function (ContextFactory $contextFactory): WorkerFactory {
        return new WorkerFactory($contextFactory);
    },

    WorkerPool::class => DI\factory(static function (int $limit): WorkerPool {
        return new DefaultWorkerPool($limit);
    })
        ->parameter('limit', DI\get('async.worker_pool.size')),
];
