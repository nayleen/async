<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream;
use Amp\Log;
use DI;
use Monolog\ErrorHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

return [
    // logger config
    'async.logger.date_format' => 'Y-m-d H:i:s.v',
    'async.logger.format' => "[%datetime%] [%channel%] [%level_name%]: %message% %context% %extra%\n",
    'async.logger.level' => static fn (DI\Container $container): int|string|Level => $container->get(
        'async.debug',
    ) ? LogLevel::DEBUG : LogLevel::INFO,
    'async.logger.name' => static fn (DI\Container $container): string => $container->get('async.app_name'),

    // logger services
    'async.logger.factory' => DI\value(static function (
        DI\Container $container,
        ByteStream\WritableStream $stream,
        string $logLevel = LogLevel::DEBUG,
        ?string $name = null,
    ): Logger {
        $logger = new Logger($name ?? (string) $container->get('async.app_name'));

        $logHandler = new Log\StreamHandler($stream, $logLevel);
        $logHandler->setFormatter($container->get(Log\ConsoleFormatter::class));

        $logger->pushHandler($logHandler);

        return $logger;
    }),

    'async.logger' => static function (DI\Container $container): LoggerInterface {
        $factory = $container->get('async.logger.factory');
        assert(is_callable($factory));

        $logger = $container->call(
            $factory,
            [
                $container,
                $container->get('async.stdout'),
                $container->get('async.logger.level'),
            ],
        );
        assert($logger instanceof LoggerInterface);

        return $logger;
    },

    'async.logger.debug' => static function (DI\Container $container): LoggerInterface {
        $factory = $container->get('async.logger.factory');
        assert(is_callable($factory));

        $logger = $container->call(
            $factory,
            [
                $container,
                $container->get('async.stderr'),
            ],
        );
        assert($logger instanceof LoggerInterface);

        return $logger;
    },

    ErrorHandler::class => DI\factory(static function (LoggerInterface $logger) {
        $errorHandler = new ErrorHandler($logger);
        $errorHandler->registerErrorHandler(errorTypes: error_reporting());
        $errorHandler->registerFatalHandler();

        return $errorHandler;
    })->parameter('logger', DI\get('async.logger.debug')),

    Log\ConsoleFormatter::class => DI\factory(static function (
        string $logFormat,
        string $dateFormat,
        bool $debug,
    ) {
        return (new Log\ConsoleFormatter($logFormat, $dateFormat, true, true))->includeStacktraces($debug);
    })
        ->parameter('logFormat', DI\get('async.logger.format'))
        ->parameter('dateFormat', DI\get('async.logger.date_format'))
        ->parameter('debug', DI\get('async.debug')),

    Logger::class => DI\get('async.logger'),
    LoggerInterface::class => DI\get(Logger::class),
];
