<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Parallel\Worker\WorkerPool;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Task\Scheduler\DefaultScheduler;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class DefaultSchedulerTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_kill_worker_pool(): void
    {
        $workerPool = $this->createMock(WorkerPool::class);
        $workerPool->expects(self::once())->method('kill');

        $kernel = TestKernel::create()->withDependency(WorkerPool::class, $workerPool);

        $scheduler = new DefaultScheduler($kernel);
        $scheduler->kill();
    }

    /**
     * @test
     */
    public function can_shutdown_worker_pool(): void
    {
        $workerPool = $this->createMock(WorkerPool::class);
        $workerPool->expects(self::once())->method('shutdown');

        $kernel = TestKernel::create()->withDependency(WorkerPool::class, $workerPool);

        $scheduler = new DefaultScheduler($kernel);
        $scheduler->shutdown();
    }
}
