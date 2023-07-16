<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DI;
use Nayleen\Async\Component\HasDependencies;

/**
 * @internal
 */
final class TestComponent extends Component implements HasDependencies
{
    public static function dependencies(): array
    {
        return [Bootstrapper::class];
    }

    public function name(): string
    {
        return 'test';
    }

    public function register(DI\ContainerBuilder $containerBuilder): void
    {
    }

    public function shutdown(Kernel $kernel): void
    {
        $kernel->writeDebug('Shutting down Dependency');
    }
}
