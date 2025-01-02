<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use DI;
use Nayleen\Async\Component;
use Nayleen\Async\Component\Bootstrapper;
use Nayleen\Async\Component\HasDependencies;
use Nayleen\Async\Kernel;

final readonly class TestComponent extends Component implements HasDependencies
{
    public static function dependencies(): iterable
    {
        yield Bootstrapper::class;
    }

    public function boot(Kernel $kernel): void
    {
        $kernel->io()->debug('Booting TestComponent');

        parent::boot($kernel);
    }

    public function name(): string
    {
        return 'test';
    }

    public function register(DI\ContainerBuilder $containerBuilder): void {}

    public function shutdown(Kernel $kernel): void
    {
        $kernel->io()->debug('Shutting down TestComponent');
    }
}
