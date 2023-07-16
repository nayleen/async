<?php

declare(strict_types = 1);

namespace Nayleen\Async\Functional\Task;

use Amp\ByteStream\WritableBuffer;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task;
use Nayleen\Async\Task\Scheduler;
use Nayleen\Async\TestKernel;
use Nayleen\Async\TestTask;

use function Amp\delay;

/**
 * @internal
 */
final class SchedulerFunctionalTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_execute_task(): void
    {
        $scheduler = new Scheduler(TestKernel::create());
        self::assertSame(69, $scheduler->execute(new TestTask()));
    }

    /**
     * @test
     */
    public function can_monitor_task(): void
    {
        $scheduler = new Scheduler(TestKernel::create());

        $task = Task::create(function () {
            $result = 0;
            while ($result < 400) {
                $result += 69;
            }

            return $result + 6;
        });

        $result = $scheduler->submit($task, monitor: true)->await();
        self::assertSame(420, $result);
    }

    /**
     * @test
     */
    public function pipes_stderr_from_child(): void
    {
        $stdErr = new WritableBuffer();

        $scheduler = new Scheduler(TestKernel::create(stdErr: $stdErr));
        $task = Task::create(fn (Kernel $kernel) => $kernel->writeDebug('Child says uhoh!'));

        $scheduler->submit($task)->finally(fn () => $stdErr->close());

        self::assertStringContainsString('Child says uhoh!', $stdErr->buffer());
    }

    /**
     * @test
     */
    public function pipes_stdout_from_child(): void
    {
        $stdOut = new WritableBuffer();

        $scheduler = new Scheduler(TestKernel::create(stdOut: $stdOut));
        $task = Task::create(fn (Kernel $kernel) => $kernel->write('info', 'Child says hi!'));

        $scheduler->submit($task)->finally(fn () => $stdOut->close());

        self::assertStringContainsString('Child says hi!', $stdOut->buffer());
    }

    /**
     * @test
     */
    public function resubmitting_cancels_previous_execution(): void
    {
        $delay = 0.001;
        $scheduler = new Scheduler(TestKernel::create(), $delay);

        $task = Task::create(function () use ($delay) {
            $result = 0;
            while ($result < 400) {
                delay($delay);
                $result += 69;
            }

            return $result + 6;
        });

        $scheduler->submit($task, monitor: true);
        delay($delay);

        $result = $scheduler->submit($task, monitor: true)->await();
        self::assertSame(420, $result);
    }
}
