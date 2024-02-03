<?php

declare(strict_types = 1);

namespace Nayleen\Async\Test;

use DI;
use Nayleen\Async\Bootstrapper;
use Nayleen\Async\Component;
use Nayleen\Async\Component\HasDependencies;
use Nayleen\Async\Kernel;
use Override;

final readonly class TestComponent extends Component implements HasDependencies
{
    #[Override]
    public static function dependencies(): iterable
    {
        yield Bootstrapper::class;
    }

    #[Override]
    public function boot(Kernel $kernel): void
    {
        $kernel->io()->debug('Booting TestComponent');

        parent::boot($kernel);
    }

    #[Override]
    public function name(): string
    {
        return 'test';
    }

    #[Override]
    public function register(DI\ContainerBuilder $containerBuilder): void {}

    #[Override]
    public function shutdown(Kernel $kernel): void
    {
        $kernel->io()->debug('Shutting down TestComponent');
    }
}
