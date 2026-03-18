<?php

declare(strict_types = 1);

use Amp\ByteStream;
use Amp\Cancellation;
use Amp\Serialization\Serializer;
use Monolog\Logger;
use Nayleen\Async\AsComponent;
use Nayleen\Async\ErrorHandler;
use Nayleen\Async\Kernel;
use Nayleen\Async\Serialization\SerializerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use Revolt\EventLoop;

return [
    // parameters
    'app.debug' => DI\exists(),
    'app.dir.cache' => DI\string('{app.dir.root}/var/cache'),
    'app.dir.root' => DI\exists(),
    'app.dir.tmp' => DI\exists(),
    'app.env' => DI\exists(),
    'app.name' => DI\env('APP_NAME', 'App'),
    'app.serializer' => DI\env('APP_SERIALIZER', null),

    // services
    'app.stderr' => DI\factory(static fn () => ByteStream\getStderr()),
    'app.stdin' => DI\factory(static fn () => ByteStream\getStdin()),
    'app.stdout' => DI\factory(static fn () => ByteStream\getStdout()),

    ByteStream\ReadableStream::class => DI\get('app.stdin'),
    ByteStream\WritableStream::class => DI\get('app.stdout'),

    Cancellation::class => DI\factory(static fn (Kernel $kernel) => $kernel->cancellation()),

    EventLoop\Driver::class => DI\factory(static function (ErrorHandler $errorHandler) {
        $driver = EventLoop::getDriver();
        $driver->setErrorHandler($errorHandler(...));

        return $driver;
    }),

    ErrorHandler::class => DI\factory(static fn (LoggerInterface $logger) => new ErrorHandler($logger)),

    Kernel::class => DI\factory(static function (ContainerInterface $c, string $env, bool $debug) {
        assert($env !== '');

        return new Kernel(
            loop: $c->get(EventLoop\Driver::class),
            logger: $c->get(Logger::class),
            env: $env,
            debug: $debug,
        );
    })
        ->parameter('env', DI\get('app.env'))
        ->parameter('debug', DI\get('app.debug')),

    LoopInterface::class => DI\factory(static fn () => Loop::get()),

    Serializer::class => DI\factory(SerializerFactory::class)
        ->parameter('type', DI\get('app.serializer')),
];
