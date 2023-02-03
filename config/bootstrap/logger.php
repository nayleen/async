<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream;
use Amp\Cluster\Internal\ClusterLogHandler;
use Amp\Log;
use Amp\Sync\Channel;
use DI;
use Monolog\ErrorHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

return [
    // logger config
    'async.logger.date_format' => 'Y-m-d H:i:s.v',
    'async.logger.format' => "[%datetime%] [%channel%] [%level_name%]: %message% %context% %extra%\n",

    // logger services
    'async.logger.factory' => DI\value(static function (
        ContainerInterface $container,
        ByteStream\WritableResourceStream $stream,
        string $logLevel = LogLevel::DEBUG,
        ?string $name = null,
    ): Logger {
        $isClusterWorker = (bool) $container->get('async.cluster.is_worker');

        if ($isClusterWorker) {
            $logHandler = new ClusterLogHandler($container->get(Channel::class), $logLevel, false);
        } else {

            $logHandler = new Log\StreamHandler($stream, $logLevel);
            $logHandler->setFormatter($container->get(Log\ConsoleFormatter::class));
        }

        $logger = new Logger($name ?? (string) $container->get('async.app_name'));
        $logger->pushHandler($logHandler);

        return $logger;
    }),

    'async.logger.stderr' => static function (DI\Container $container): LoggerInterface {
        $factory = $container->get('async.logger.factory');
        assert(is_callable($factory));

        $logger = $container->call(
            $factory,
            [
                $container,
                $container->make('async.stderr'),
            ],
        );
        assert($logger instanceof LoggerInterface);

        return $logger;
    },

    'async.logger.stdout' => static function (DI\Container $container): LoggerInterface {
        $factory = $container->get('async.logger.factory');
        assert(is_callable($factory));

        $logger = $container->call(
            $factory,
            [
                $container,
                $container->make('async.stdout'),
                $container->get('async.debug') ? LogLevel::DEBUG : LogLevel::INFO,
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
    })
        ->parameter('logger', DI\get('async.logger.stderr')),

    Log\ConsoleFormatter::class => DI\factory(static function (
        string $logFormat,
        string $dateFormat,
        bool $debug,
    ) {
        return (new Log\ConsoleFormatter($logFormat, $dateFormat, true, true))
            ->includeStacktraces($debug);
    })
        ->parameter('logFormat', DI\get('async.logger.format'))
        ->parameter('dateFormat', DI\get('async.logger.date_format'))
        ->parameter('debug', DI\get('async.debug')),

    Logger::class => DI\get('async.logger.stdout'),
    LoggerInterface::class => DI\get(Logger::class),
];
