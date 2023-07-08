<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Components;
use Nayleen\Async\Kernel as BaseKernel;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;

/**
 * @internal
 */
final class Kernel extends BaseKernel
{
    public static function create(
        ?EventLoop\Driver $loop = null,
        ?LoggerInterface $stdErrLogger = null,
        ?LoggerInterface $stdOutLogger = null,
    ): self {
        return (new self())
            ->withDependency(EventLoop\Driver::class, $loop ?? EventLoop::getDriver())
            ->withDependency('async.logger.stderr', $stdErrLogger ?? new NullLogger())
            ->withDependency('async.logger.stdout', $stdOutLogger ?? new NullLogger());
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
