<?php

declare(strict_types = 1);

use Amp\ByteStream;
use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

return [
    // parameters
    'log.date_format' => DI\env('LOG_DATE_FORMAT', 'Y-m-d H:i:s.v'),
    'log.level' => DI\factory(static fn (?string $logLevel, bool $debug): string => $logLevel ?? ($debug ? LogLevel::DEBUG : LogLevel::INFO))
        ->parameter('logLevel', DI\env('LOG_LEVEL', null))
        ->parameter('debug', DI\get('app.debug')),

    'log.line_format' => DI\factory(static fn (string $format) => rtrim($format) . PHP_EOL)
        ->parameter('format', DI\env('LOG_LINE_FORMAT', '%datetime% [%channel%] [%level_name%] %message% %context% %extra%')),

    // services
    'log.handlers' => DI\add([
        DI\get(StreamHandler::class),
    ]),

    Logger::class => DI\factory(static function (ContainerInterface $c): Logger {
        $name = $c->get('app.name');
        assert(is_string($name));

        $logger = new Logger($name);

        $logHandlers = $c->get('log.handlers');
        assert(is_iterable($logHandlers));

        foreach ($logHandlers as $handler) {
            assert($handler instanceof HandlerInterface);
            $logger->pushHandler($handler);
        }

        $dateFormat = $c->get('log.date_format');
        assert(is_string($dateFormat));

        $logger->pushProcessor(new PsrLogMessageProcessor(
            dateFormat: $dateFormat,
            removeUsedContextFields: true,
        ));

        return $logger->useLoggingLoopDetection(false);
    }),
    LoggerInterface::class => DI\get(Logger::class),

    StreamHandler::class => DI\factory(static function (ContainerInterface $c): StreamHandler {
        $sink = $c->get('app.stderr');
        assert($sink instanceof ByteStream\WritableStream);

        $logLevel = $c->get('log.level');
        assert(is_string($logLevel));

        $logLevel = Logger::toMonologLevel($logLevel); // @phpstan-ignore-line

        $handler = new StreamHandler(
            sink: $sink,
            level: $logLevel,
            bubble: true,
        );

        $dateFormat = $c->get('log.date_format');
        assert(is_string($dateFormat));

        $lineFormat = $c->get('log.line_format');
        assert(is_string($lineFormat));

        $handler->setFormatter(new ConsoleFormatter(
            format: $lineFormat,
            dateFormat: $dateFormat,
            allowInlineLineBreaks: false,
            ignoreEmptyContextAndExtra: true,
        ));

        return $handler;
    }),
];
