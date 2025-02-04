<?php

declare(strict_types = 1);

namespace Nayleen\Async\App;

use Amp\Cluster\ClusterWatcher;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Amp\TimeoutCancellation;
use Nayleen\Async\Kernel;
use Nayleen\Async\Worker;
use SplObjectStorage;

/**
 * @psalm-internal Nayleen\Async
 */
class Workers
{
    use ForbidCloning;
    use ForbidSerialization;

    /**
     * @var SplObjectStorage<Worker, ClusterWatcher>
     */
    public readonly SplObjectStorage $watchers;

    /**
     * @var SplObjectStorage<Worker, positive-int>
     */
    public readonly SplObjectStorage $workers;

    public function __construct()
    {
        $this->watchers = new SplObjectStorage();
        $this->workers = new SplObjectStorage();
    }

    /**
     * @param positive-int $count
     */
    public function add(Worker $worker, int $count = 1): void
    {
        assert($count >= 1);
        $this->workers->attach($worker, $count);
    }

    public function start(Kernel $kernel): void
    {
        foreach ($this->workers as $worker) {
            $count = $this->workers[$worker];
            assert(is_int($count) && $count >= 1);

            $watcher = $kernel->container()->make(ClusterWatcher::class);
            assert($watcher instanceof ClusterWatcher);

            $watcher->start($count);
            $watcher->broadcast($worker);

            $this->watchers->attach($worker, $watcher);
        }
    }
}
