<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Parallel\Worker\WorkerPool;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 * @small
 */
final class SchedulerTest extends AsyncTestCase
{
    private function createScheduler(TestKernel $kernel): Scheduler
    {
        return new Scheduler($kernel);
    }

    /**
     * @test
     */
    public function can_kill_worker_pool(): void
    {
        $workerPool = $this->createMock(WorkerPool::class);
        $workerPool->expects(self::once())->method('kill');

        $kernel = TestKernel::create([WorkerPool::class => $workerPool]);

        $scheduler = $this->createScheduler($kernel);
        $scheduler->kill();
    }

    /**
     * @test
     */
    public function can_shutdown_worker_pool(): void
    {
        $workerPool = $this->createMock(WorkerPool::class);
        $workerPool->expects(self::once())->method('shutdown');

        $kernel = TestKernel::create([WorkerPool::class => $workerPool]);

        $scheduler = $this->createScheduler($kernel);
        $scheduler->shutdown();
    }
}
