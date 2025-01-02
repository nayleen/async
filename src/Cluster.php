<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Cluster\ClusterWatcher;
use InvalidArgumentException;
use Override;

use function Amp\Cluster\countCpuCores;

readonly class Cluster extends Worker
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
    public function __construct(Worker $worker, ?int $count = null)
    {
        assert(
            !($worker instanceof static),
            new InvalidArgumentException(),
        );

        $count ??= countCpuCores();
        assert($count > 0);

        $this->count = $count;
        $this->worker = $worker;

        parent::__construct(static fn () => null);
    }

    private function watcher(Kernel $kernel): ClusterWatcher
    {
        assert(file_exists(self::RUNNER_SCRIPT));

        $watcher = $kernel->container()->make(ClusterWatcher::class, ['script' => self::RUNNER_SCRIPT]);
        assert($watcher instanceof ClusterWatcher);

        return $watcher;
    }

    #[Override]
    protected function execute(Kernel $kernel): null
    {
        $watcher = $this->watcher($kernel);

        try {
            $watcher->start($this->count);
            $watcher->broadcast($this->worker);

            return parent::execute($kernel);
        } finally {
            $watcher->stop($kernel->cancellation);
        }
    }
}
