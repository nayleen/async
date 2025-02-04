<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cluster\ClusterWatcher;
use Amp\ForbidCloning;
use Amp\ForbidSerialization;

use function Amp\Cluster\countCpuCores;

readonly class Cluster extends Runtime
{
    use ForbidCloning;
    use ForbidSerialization;

    /**
     * @var positive-int
     */
    private int $count;

    private Worker $worker;

    /**
     * @param positive-int|null $count
     */
    public function __construct(Worker $worker, ?int $count = null, ?Kernel $kernel = null)
    {
        $count ??= countCpuCores();
        assert($count > 0);

        $this->count = $count;
        $this->worker = $worker;

        parent::__construct($this->start(...), $kernel);
    }

    private function start(Kernel $kernel): int
    {
        $watcher = $kernel->container()->make(ClusterWatcher::class);
        assert($watcher instanceof ClusterWatcher);

        $watcher->start($this->count);
        $watcher->broadcast($this->worker);

        $kernel->trap();

        return 0;
    }
}
