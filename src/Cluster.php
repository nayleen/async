<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cluster\ClusterWatcher;
use Override;
use Throwable;

use function Amp\Cluster\countCpuCores;

readonly class Cluster extends Runtime
{
    /**
     * @var positive-int
     */
    private int $count;

    private Worker $worker;

    /**
     * @var non-empty-string
     */
    private const string RUNNER_SCRIPT = __DIR__ . '/Worker/Internal/cluster-runner.php';

    /**
     * @param positive-int|null $count
     */
    public function __construct(Worker $worker, ?int $count = null, ?Kernel $kernel = null)
    {
        $count ??= countCpuCores();
        assert($count > 0);

        $this->count = $count;
        $this->worker = $worker;

        parent::__construct(static fn () => null, $kernel);
    }

    private function watcher(Kernel $kernel): ClusterWatcher
    {
        assert(file_exists(self::RUNNER_SCRIPT));

        $watcher = $kernel->container()->make(ClusterWatcher::class, ['script' => self::RUNNER_SCRIPT]);
        assert($watcher instanceof ClusterWatcher);

        return $watcher;
    }

    /**
     * @return int<0, 255>
     */
    #[Override]
    protected function execute(Kernel $kernel): int
    {
        $watcher = $this->watcher($kernel);

        try {
            $watcher->start($this->count);
            $watcher->broadcast($this->worker);

            $kernel->trap();
        } catch (Throwable) {
            return 1;
        } finally {
            $watcher->stop($kernel->cancellation);
        }

        return 0;
    }
}
