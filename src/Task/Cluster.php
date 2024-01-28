<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Cluster as AmpCluster;
use InvalidArgumentException;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task;
use Nayleen\Async\Timers;
use RuntimeException;

class Cluster extends Worker
{
    /**
     * @var positive-int
     */
    private readonly int $count;

    /**
     * @var non-empty-string
     */
    private const RUNNER_SCRIPT = __DIR__ . '/Internal/cluster-runner.php';

    /**
     * @param positive-int|null $count
     */
    public function __construct(private readonly Task $task, ?int $count = null)
    {
        assert(
            class_exists(AmpCluster\Cluster::class),
            new RuntimeException('Running workers in a cluster requires amphp/cluster:^2 to be installed'),
        );

        assert(
            !($task instanceof self),
            new InvalidArgumentException(),
        );

        $count ??= AmpCluster\countCpuCores();
        assert($count > 0);

        $this->count = $count;

        parent::__construct(new Timers());
    }

    private function watcher(Kernel $kernel): AmpCluster\ClusterWatcher
    {
        $watcher = $kernel->container()->make(AmpCluster\ClusterWatcher::class, ['script' => self::RUNNER_SCRIPT]);
        assert($watcher instanceof AmpCluster\ClusterWatcher);

        return $watcher;
    }

    protected function execute(Kernel $kernel): null
    {
        $watcher = $this->watcher($kernel);

        try {
            $watcher->start($this->count);
            $watcher->broadcast($this->task);

            return parent::execute($kernel);
        } finally {
            $watcher->stop($kernel->cancellation);
        }
    }
}
