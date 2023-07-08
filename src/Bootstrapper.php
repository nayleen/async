<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DI\ContainerBuilder;
use Nayleen\Async\Recommender\Performance;

/**
 * @internal
 */
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
        // set container builder defaults
        $containerBuilder->useAttributes(false);
        $containerBuilder->useAutowiring(true);

        $configPath = dirname(__DIR__) . '/config';
        assert(file_exists($configPath) && is_dir($configPath));

        $this->load($containerBuilder, $configPath . '/*.php');
    }
}
