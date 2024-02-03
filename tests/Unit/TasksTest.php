<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Future;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Task\Scheduler;
use Nayleen\Async\Test\NoopWorker;

/**
 * @internal
 * @small
 *
 * @covers \Nayleen\Async\Tasks
 * @covers \Nayleen\Async\Test\NoopWorker
 */
final class TasksTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function does_nothing_when_empty(): void
    {
        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects(self::never())->method('submit');

        $tasks = new Tasks();
        $tasks->submit($scheduler);
    }

    /**
     * @test
     */
    public function schedules_registered_tasks(): void
    {
        $task = new NoopWorker();
        $tasks = new Tasks($task);

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects(self::once())->method('submit')->with($task)->willReturn(Future::complete());

        $tasks->submit($scheduler);
    }
}
