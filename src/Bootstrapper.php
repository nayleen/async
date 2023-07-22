<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DI\ContainerBuilder;
use Nayleen\Async\Recommender\Performance;

class Bootstrapper extends Component
{
    private const NAME = 'bootstrap';

    public function boot(Kernel $kernel): void
    {
        Performance::recommend($kernel);

        $loop = $kernel->loop();
        $loop->unreference($loop->onSignal(SIGUSR1, static fn () => $kernel->reload()));
        $loop->unreference($loop->onSignal(SIGINT, static fn () => $kernel->stop(SIGINT)));
        $loop->unreference($loop->onSignal(SIGTERM, static fn () => $kernel->stop(SIGTERM)));
    }

    public function name(): string
    {
        return self::NAME;
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        $configPath = dirname(__DIR__) . '/config';
        assert(file_exists($configPath) && is_dir($configPath));

        $this->load($containerBuilder, $configPath . '/*.php');
    }

    public function reload(Kernel $kernel): void
    {
        $kernel->scheduler->shutdown();
    }

    public function shutdown(Kernel $kernel): void
    {
        $kernel->scheduler->shutdown();
    }
}
