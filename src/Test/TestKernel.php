<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Amp\ByteStream\WritableBuffer;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Amp\NullCancellation;
use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Component\Finder;
use Nayleen\Async\Components;
use Nayleen\Async\Kernel;
use Revolt\EventLoop;

/**
 * @psalm-internal Nayleen\Async
 */
final class TestKernel extends Kernel
{
    public function __construct(
        iterable $components = new Finder(),
        Cancellation $cancellation = new NullCancellation(),
    ) {
        parent::__construct($components, null, $cancellation);
    }

    public static function create(
        ?EventLoop\Driver $loop = null,
        Cancellation $cancellation = new NullCancellation(),
        WritableStream $stdOut = new WritableBuffer(),
        WritableStream $stdErr = new WritableBuffer(),
    ): self {
        $logger = new Logger('TestKernel');
        $logger->pushHandler(new NullHandler());

        return (new self(cancellation: $cancellation))
            ->withDependency(EventLoop\Driver::class, $loop ?? EventLoop::getDriver())
            ->withDependency(Logger::class, $logger)
            ->withDependency('async.stderr', $stdErr)
            ->withDependency('async.stdout', $stdOut);
    }

    public function withDependency(string $name, mixed $value): self
    {
        return new self(
            new Components(
                [
                    ...$this->components,
                    DependencyProvider::create([$name => $value]),
                ],
            ),
        );
    }
}
