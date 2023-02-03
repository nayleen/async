<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use DI\ContainerBuilder;
use Nayleen\Async\Exception\ReloadException;
use Nayleen\Async\Exception\StopException;
use Nayleen\Async\Recommender\Performance;

/**
 * @internal
 */
final class Bootstrapper extends Component
{
    public function boot(Kernel $kernel): void
    {
        Performance::recommend($kernel);

        $loop = $kernel->loop();
        $loop->unreference($loop->onSignal(SIGUSR1, static fn () => throw new ReloadException()));
        $loop->unreference($loop->onSignal(SIGINT, static fn () => throw new StopException()));
        $loop->unreference($loop->onSignal(SIGTERM, static fn () => throw new StopException()));
    }

    public function name(): string
    {
        return 'bootstrap';
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        // set container builder defaults
        $containerBuilder->useAttributes(false);
        $containerBuilder->useAutowiring(true);

        $configPath = dirname(__DIR__) . '/config/bootstrap';
        assert(file_exists($configPath) && is_dir($configPath));

        $this->load($containerBuilder, $configPath . '/async.php');
        $this->load($containerBuilder, $configPath . '/cache.php');
        $this->load($containerBuilder, $configPath . '/console.php');
        $this->load($containerBuilder, $configPath . '/logger.php');
        $this->load($containerBuilder, $configPath . '/redis.php');
    }
}
