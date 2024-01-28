<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Dns;
use Amp\Parallel;
use Amp\Socket;
use DI\ContainerBuilder;
use Nayleen\Async\Component\Recommender;

readonly class Bootstrapper extends Component
{
    private function setupLoop(Kernel $kernel): void
    {
        Dns\dnsResolver($kernel->container()->get(Dns\DnsResolver::class));
        Parallel\Context\contextFactory($kernel->container()->get(Parallel\Context\ContextFactory::class));
        Parallel\Worker\workerFactory($kernel->container()->get(Parallel\Worker\WorkerFactory::class));
        Parallel\Worker\workerPool($kernel->container()->get(Parallel\Worker\WorkerPool::class));
        Socket\socketConnector($kernel->container()->get(Socket\SocketConnector::class));
    }

    /**
     * @return iterable<Recommender>
     */
    protected function recommenders(Kernel $kernel): iterable
    {
        yield new Recommender\Assertions();
        yield new Recommender\Xdebug();
    }

    public function boot(Kernel $kernel): void
    {
        assert($kernel->io()->info('Booting Kernel'));

        $this->setupLoop($kernel);

        parent::boot($kernel);
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        $configPath = dirname(__DIR__) . '/config';
        assert(file_exists($configPath) && is_dir($configPath));

        $this->load($containerBuilder, $configPath . '/*.php');
    }

    public function shutdown(Kernel $kernel): void
    {
        assert($kernel->io()->info('Shutting down Kernel'));

        $kernel->scheduler->shutdown();
    }
}
