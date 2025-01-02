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
    'async.advisories' => DI\factory(static function (string $runAdvisories, bool $isWorker): bool {
        $runAdvisories = (bool) filter_var($runAdvisories, FILTER_VALIDATE_BOOL);

        return $runAdvisories && !$isWorker;
    })
        ->parameter('runAdvisories', DI\env('ASYNC_ADVISORIES', '1'))
        ->parameter('isWorker', DI\get('async.worker')),

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

    // app services
    'async.exceptions.handler' => static function (ErrorHandler $errorHandler): Closure {
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
        ->parameter('errorHandler', DI\get('async.exceptions.handler')),

    IO::class => DI\factory(static fn (
        ByteStream\ReadableStream $input,
        ByteStream\WritableStream $output,
        Logger $logger,
    ): IO => new IO($input, $output, $logger))
        ->parameter('input', DI\get('async.stdin'))
        ->parameter('output', DI\get('async.stdout')),

    ServerSocketFactory::class => static fn (): ServerSocketFactory => Cluster::getServerSocketFactory(),

    SocketConnector::class => static fn (DnsResolver $dnsResolver): SocketConnector => new RetrySocketConnector(new DnsSocketConnector($dnsResolver)),
];
