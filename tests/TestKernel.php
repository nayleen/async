<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\WritableBuffer;
use Amp\ByteStream\WritableStream;
use Amp\Cancellation;
use Amp\NullCancellation;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Component\Finder;
use Revolt\EventLoop;

/**
 * @internal
 */
final class TestKernel extends Kernel
{
    private function __construct(
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
        return (new self(cancellation: $cancellation))
            ->withDependency(EventLoop\Driver::class, $loop ?? EventLoop::getDriver())
            ->withDependency('async.stderr', $stdErr)
            ->withDependency('async.stdout', $stdOut);
    }

    public function withDependency(string $name, mixed $value): self
    {
        return new self(
            new Components(
                [
                    DependencyProvider::create([$name => $value]),
                    ...$this->components,
                ],
            ),
        );
    }
}
