<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream;
use Amp\Cluster\Cluster;
use Amp\Dns\DnsResolver;
use Amp\Serialization\Serializer;
use Amp\Socket\DnsSocketConnector;
use Amp\Socket\RetrySocketConnector;
use Amp\Socket\ServerSocketFactory;
use Amp\Socket\SocketConnector;
use Amp\Sync\Channel;
use Closure;
use DI;
use Monolog\ErrorHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;
use Throwable;

use function Amp\Dns\createDefaultResolver;

return [
    // app config
    'async.app_name' => DI\env('ASYNC_APP_NAME', 'Kernel'),
    'async.app_version' => DI\env('ASYNC_APP_VERSION', 'UNKNOWN'),
    'async.debug' => DI\factory(static function (string $env, string $debug): bool {
        $debug = (bool) filter_var($debug, FILTER_VALIDATE_BOOL);

        return $debug || $env !== 'prod';
    })
        ->parameter('env', DI\get('async.env'))
        ->parameter('debug', DI\env('ASYNC_DEBUG', '0')),

    'async.env' => DI\factory(static fn (string $env): string => strtolower($env))
        ->parameter('env', DI\env('ASYNC_ENV', 'prod')),

    'async.run_recommendations' => DI\factory(static function (string $wantsRecommendations, bool $isWorker): bool {
        $wantsRecommendations = (bool) filter_var($wantsRecommendations, FILTER_VALIDATE_BOOL);

        return $wantsRecommendations && !$isWorker;
    })
        ->parameter('wantsRecommendations', DI\env('ASYNC_RECOMMENDATIONS', '1'))
        ->parameter('isWorker', DI\get('async.worker')),

    // directories
    'async.dir.base' => DI\env('ASYNC_DIR'),
    'async.dir.cache' => DI\env('ASYNC_CACHE_DIR', sys_get_temp_dir()),
    'async.dir.tmp' => DI\env('ASYNC_TMP_DIR', sys_get_temp_dir()),

    // app services
    'async.exception_handler' => static function (ErrorHandler $errorHandler): Closure {
        $errorHandler->registerExceptionHandler();
        $exceptionHandler = set_exception_handler(null);
        assert(is_callable($exceptionHandler));

        set_exception_handler($exceptionHandler);

        return $exceptionHandler(...);
    },

    'async.stderr' => static fn (): ByteStream\WritableStream => ByteStream\getStderr(),
    'async.stdin' => static fn (): ByteStream\ReadableStream => ByteStream\getStdin(),
    'async.stdout' => static fn (): ByteStream\WritableStream => ByteStream\getStdout(),

    Channel::class => DI\decorate(static function (
        ?Channel $channel,
        DI\Container $container,
    ): Channel {
        if ($channel !== null) {
            return $channel;
        }

        if (Cluster::isWorker()) {
            return Cluster::getChannel();
        }

        return new ByteStream\StreamChannel(
            $container->get('async.stdin'),
            $container->get('async.stdout'),
            $container->get(Serializer::class),
        );
    }),

    DnsResolver::class => static fn (): DnsResolver => createDefaultResolver(),

    ErrorHandler::class => static function (LoggerInterface $logger): ErrorHandler {
        $errorHandler = new ErrorHandler($logger);
        $errorHandler->registerErrorHandler(errorTypes: error_reporting());
        $errorHandler->registerFatalHandler();

        return $errorHandler;
    },

    EventLoop\Driver::class => DI\factory(static function (Closure $errorHandler): EventLoop\Driver {
        /**
         * @var Closure(Throwable): void $errorHandler
         */
        $driver = EventLoop::getDriver();
        $driver->setErrorHandler($errorHandler);

        return $driver;
    })
        ->parameter('errorHandler', DI\get('async.exception_handler')),

    IO::class => DI\factory(static function (
        ByteStream\ReadableStream $input,
        ByteStream\WritableStream $output,
        Logger $logger,
    ): IO {
        return new IO($input, $output, $logger);
    })
        ->parameter('input', DI\get('async.stdin'))
        ->parameter('output', DI\get('async.stdout')),

    ServerSocketFactory::class => static fn (): ServerSocketFactory => Cluster::getServerSocketFactory(),

    SocketConnector::class => static fn (DnsResolver $dnsResolver): SocketConnector => new RetrySocketConnector(new DnsSocketConnector($dnsResolver)),
];
