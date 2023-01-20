<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Amp\Cancellation;
use DI\ContainerBuilder;
use Nayleen\Async\Component;
use Nayleen\Async\Kernel;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;

final class Bootstrapper extends Component
{
    public function boot(ContainerInterface $container, Cancellation $cancellation): void
    {
        $loop = $container->get(EventLoop\Driver::class);
        $kernel = $container->get(Kernel::class);

        $loop->unreference($loop->onSignal(SIGUSR1, $kernel->reload(...)));
        $loop->unreference($loop->onSignal(SIGINT, $kernel->stop(...)));
        $loop->unreference($loop->onSignal(SIGTERM, $kernel->stop(...)));

        $env = (string) $container->get('app.env');
        assert($env !== '');

        PerformanceRecommender::execute($container->get(LoggerInterface::class), $env);
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

        $configPath = dirname(__DIR__, 2) . '/config/bootstrap';
        assert(file_exists($configPath) && is_dir($configPath));

        $this->load($containerBuilder, $configPath . '/app.php');
        $this->load($containerBuilder, $configPath . '/console.php');
        $this->load($containerBuilder, $configPath . '/logger.php');
    }
}
