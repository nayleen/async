<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Future;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Task\Scheduler;

/**
 * @internal
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
        $tasks->schedule($scheduler);
    }

    /**
     * @test
     */
    public function schedules_registered_tasks(): void
    {
        $task = $this->createStub(Task::class);
        $tasks = new Tasks($task);

        $scheduler = $this->createMock(Scheduler::class);
        $scheduler->expects(self::once())->method('submit')->with($task, null, true)->willReturn(Future::complete());

        $tasks->schedule($scheduler);
    }
}
