<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream;
use Amp\Serialization\Serializer;
use Amp\Socket\ResourceServerSocketFactory;
use Amp\Socket\ServerSocketFactory;
use Amp\Sync\Channel;
use Closure;
use DI;
use Monolog\ErrorHandler;
use Revolt\EventLoop;
use Throwable;

return [
    // app config
    'async.app_name' => DI\env('ASYNC_APP_NAME', 'Kernel'),
    'async.app_version' => DI\env('ASYNC_APP_VERSION', 'UNKNOWN'),
    'async.debug' => DI\factory(
        static fn (string $env): bool => (bool) (getenv('ASYNC_DEBUG') ?: $env !== 'prod'),
    )->parameter('env', DI\get('async.env')),
    'async.env' => strtolower((getenv('ASYNC_ENV') ?: 'prod')),

    // directories
    'async.dir.base' => DI\env('ASYNC_DIR'),
    'async.dir.cache' => DI\env('ASYNC_TMP_DIR', sys_get_temp_dir()),

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
        DI\Container $container,
    ): Channel {
        return $channel ?? new ByteStream\StreamChannel(
            $container->get('async.stdin'),
            $container->get('async.stdout'),
            $container->get(Serializer::class),
        );
    }),

    EventLoop\Driver::class => DI\factory(static function (Closure $errorHandler, bool $debug): EventLoop\Driver {
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

    ServerSocketFactory::class => static fn (): ServerSocketFactory => new ResourceServerSocketFactory(),
];
