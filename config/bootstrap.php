<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream;
use Amp\Log;
use DI;
use Monolog\ErrorHandler;
use Monolog\Logger;
use Nayleen\Async\Console\Output;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Revolt\EventLoop;
use Symfony\Component\Console\Output\OutputInterface;

return static function (DI\ContainerBuilder $containerBuilder) {
    // set container builder defaults
    $containerBuilder->ignorePhpDocErrors(true);
    $containerBuilder->useAnnotations(false);
    $containerBuilder->useAutowiring(true);

    $containerBuilder->addDefinitions([
        // app config parameters
        'app.debug' => ((bool) getenv('APP_DEBUG')) !== false,
        'app.env' => strtolower((getenv('APP_ENV') ?: 'prod')),
        'app.name' => DI\env('APP_NAME', 'Kernel'),

        'logger.date_format' => 'Y-m-d H:i:s.v',
        'logger.format' => "[%datetime%] [%channel%] [%level_name%]: %message% %context% %extra%\n",

        // directories
        'dir.base' => DI\env('APP_DIR'),
        'dir.cache' => fn () => sys_get_temp_dir(),

        // services
        'app.exception_handler' => function (ErrorHandler $errorHandler) {
            $errorHandler->registerExceptionHandler();

            $exceptionHandler = set_exception_handler(null);
            set_exception_handler($exceptionHandler);

            return $exceptionHandler;
        },

        'console.output' => function (ContainerInterface $c) {
            return new Output($c->get('stream.stdout'));
        },

        'logger.factory' => DI\value(function (
            ContainerInterface $container,
            ByteStream\WritableResourceStream $stream,
            string $name,
            string $logLevel = LogLevel::DEBUG
        ) {
            $streamHandler = new Log\StreamHandler($stream, $logLevel);
            $streamHandler->setFormatter(
                (new Log\ConsoleFormatter(
                    $container->get('logger.format'),
                    $container->get('logger.date_format'),
                    true,
                    true,
                ))->includeStacktraces($container->get('app.debug')),
            );

            $logger = new Logger($name);
            $logger->pushHandler($streamHandler);

            return $logger;
        }),

        'logger.stderr' => function (DI\Container $container) {
            return $container->call(
                $container->get('logger.factory'),
                [
                    $container,
                    $container->make('stream.stderr'),
                    $container->get('app.name'),
                ]
            );
        },

        'logger.stdout' => function (DI\Container $container) {
            return $container->call(
                $container->get('logger.factory'),
                [
                    $container,
                    $container->make('stream.stdout'),
                    $container->get('app.name'),
                    $container->get('app.debug') ? LogLevel::DEBUG : LogLevel::INFO,
                ]
            );
        },

        'stream.stderr' => fn () => ByteStream\getStderr(),
        'stream.stdout' => fn () => ByteStream\getStdout(),

        ErrorHandler::class => DI\factory(function (LoggerInterface $logger) {
            $errorHandler = new ErrorHandler($logger);
            $errorHandler->registerErrorHandler(errorTypes: error_reporting());
            $errorHandler->registerFatalHandler();
            
            return $errorHandler;
        })->parameter('logger', DI\get('logger.stderr')),

        EventLoop\Driver::class => DI\factory(function (callable $errorHandler, bool $debug) {
            $driver = (new EventLoop\DriverFactory())->create();

            if (!($driver instanceof EventLoop\Driver\TracingDriver) && $debug) {
                $driver = new EventLoop\Driver\TracingDriver($driver);
            }
            
            $driver->setErrorHandler($errorHandler);

            return $driver;
        })
            ->parameter('errorHandler', DI\get('app.exception_handler'))
            ->parameter('debug', DI\get('app.debug')),

        Logger::class => DI\get('logger.stdout'),
        LoggerInterface::class => DI\get(Logger::class),
        OutputInterface::class => DI\get('console.output'),
    ]);
};
