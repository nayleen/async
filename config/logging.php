<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Log\StreamHandler;
use DI;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Level;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

return [
    // logger config
    'async.logger.date_format' => 'Y-m-d H:i:s.v',
    'async.logger.format' => "[%datetime%] [%channel%] [%level_name%]: %message% %context% %extra%\n",
    'async.logger.level' => DI\factory(static fn (bool $debug): int|Level|string => $debug ? LogLevel::DEBUG : LogLevel::INFO)
        ->parameter('debug', DI\get('async.debug')),
    'async.logger.name' => static fn (DI\Container $container): string => $container->get('async.app_name'),

    // logger services
    LineFormatter::class => DI\factory(static fn (string $logFormat, string $dateFormat, bool $debug) => (new LineFormatter($logFormat, $dateFormat, true, true))->includeStacktraces($debug))
        ->parameter('logFormat', DI\get('async.logger.format'))
        ->parameter('dateFormat', DI\get('async.logger.date_format'))
        ->parameter('debug', DI\get('async.debug')),

    Logger::class => static function (DI\Container $container): Logger {
        $logger = new Logger((string) $container->get('async.app_name'));
        $logger->useLoggingLoopDetection(false);

        $logHandler = new StreamHandler($container->get('async.stderr'), $container->get('async.logger.level'));
        $logHandler->setFormatter($container->get(LineFormatter::class));

        if ((bool) $container->get('async.worker')) {
            $logHandler = new FingersCrossedHandler(
                $logHandler,
                $container->get('async.worker.log_threshold'),
                bubble: false,
            );
        }

        $logger->pushHandler($logHandler);

        return $logger;
    },

    LoggerInterface::class => DI\get(Logger::class),
];
