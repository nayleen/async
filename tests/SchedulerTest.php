<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Task\Scheduler;
use Nayleen\Async\Test\Kernel as TestKernel;
use Nayleen\Async\Test\Runtime as TestRuntime;

/**
 * @internal
 */
final class SchedulerTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_kill_scheduler_with_running_tasks(): void
    {
        $scheduler = new Scheduler(TestKernel::create());
        $scheduler->submit(new TestRuntime(0.25));
        self::assertTrue($scheduler->running());

        $scheduler->kill();
        self::assertFalse($scheduler->running());
    }

    /**
     * @test
     */
    public function can_shutdown_scheduler_with_running_tasks(): void
    {
        $scheduler = new Scheduler(TestKernel::create());
        $scheduler->submit(new TestRuntime(0.25));
        self::assertTrue($scheduler->running());

        $scheduler->shutdown();
        self::assertFalse($scheduler->running());
    }
}
