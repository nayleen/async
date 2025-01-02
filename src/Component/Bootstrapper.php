<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Amp\Dns;
use Amp\Parallel;
use Amp\Socket;
use DI\ContainerBuilder;
use Nayleen\Async\Component;
use Nayleen\Async\Kernel;
use Override;

readonly class Bootstrapper extends Component
{
    private function setupLoop(Kernel $kernel): void
    {
        assert($kernel->io()->debug('Booting Kernel'));

        Dns\dnsResolver($kernel->container()->get(Dns\DnsResolver::class));
        Parallel\Context\contextFactory($kernel->container()->get(Parallel\Context\ContextFactory::class));
        Parallel\Worker\workerFactory($kernel->container()->get(Parallel\Worker\WorkerFactory::class));
        Parallel\Worker\workerPool($kernel->container()->get(Parallel\Worker\WorkerPool::class));
        Socket\socketConnector($kernel->container()->get(Socket\SocketConnector::class));
    }

    private function setupMessageBus(Kernel $kernel): void
    {
        assert($kernel->io()->debug('Starting Message Bus'));
    }

    /**
     * @return iterable<Advisory>
     */
    public function advisories(Kernel $kernel): iterable
    {
        yield new Advisory\Assertions();
        yield new Advisory\Xdebug();
    }

    public function boot(Kernel $kernel): void
    {
        $this->setupLoop($kernel);
        $this->setupMessageBus($kernel);

        parent::boot($kernel);
    }

    #[Override]
    public function register(ContainerBuilder $containerBuilder): void
    {
        $configPath = dirname(__DIR__, 2) . '/config';
        assert(file_exists($configPath) && is_dir($configPath));

        $this->load($containerBuilder, $configPath . '/*.php');
    }

    public function shutdown(Kernel $kernel): void
    {
        assert($kernel->io()->debug('Shutting down Kernel'));

        $kernel->scheduler->shutdown();
    }
}
