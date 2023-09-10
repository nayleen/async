<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\ByteStream\WritableBuffer;
use Amp\PHPUnit\AsyncTestCase;
use Amp\Sync\LocalMutex;
use Amp\Sync\SharedMemoryParcel;
use DI;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task;
use Nayleen\Async\Test\TestKernel;
use Nayleen\Async\Test\TestTask;
use RuntimeException;

use function Amp\delay;

/**
 * @internal
 */
final class SchedulerFunctionalTest extends AsyncTestCase
{
    private function createScheduler(TestKernel $kernel): Scheduler
    {
        return new Scheduler($kernel, 0, 0);
    }

    /**
     * @test
     */
    public function can_run_task(): void
    {
        $scheduler = $this->createScheduler(TestKernel::create());
        self::assertSame(69, $scheduler->run(new TestTask()));
    }

    /**
     * @test
     */
    public function pipes_stderr_from_child(): void
    {
        $stdErr = new WritableBuffer();

        $scheduler = $this->createScheduler(TestKernel::create(stdErr: $stdErr));
        $task = Task::create(fn (Kernel $kernel) => $kernel->io()->debug('Child says uhoh!'));

        $scheduler->submit($task)->finally(fn () => $stdErr->close());

        self::assertStringContainsString('Child says uhoh!', $stdErr->buffer());
    }

    /**
     * @test
     */
    public function resubmitting_cancels_previous_execution(): void
    {
        $delay = 0.001;
        $scheduler = $this->createScheduler(TestKernel::create());

        $task = Task::create(function () use ($delay) {
            $result = 0;
            while ($result < 400) {
                delay($delay);
                $result += 69;
            }

            return $result + 6;
        });

        $scheduler->submit($task);
        delay($delay);

        $result = $scheduler->submit($task)->await();
        self::assertSame(420, $result);
    }

    /**
     * @test
     */
    public function will_bail_with_null_on_too_many_retries(): void
    {
        $kernel = TestKernel::create()->withDependency('async.exception_handler', DI\value(fn () => null));
        $scheduler = $this->createScheduler($kernel);

        $task = Task::create(fn () => throw new RuntimeException());

        $result = $scheduler->submit($task)->await();
        self::assertNull($result);
    }

    /**
     * @test
     */
    public function will_retry_failing_tasks(): void
    {
        $expectedRetries = 3;

        $shm = SharedMemoryParcel::create(new LocalMutex(), 0);
        $key = $shm->getKey();

        $kernel = TestKernel::create()->withDependency('async.exception_handler', DI\value(fn () => null));
        $scheduler = new Scheduler($kernel, $expectedRetries - 1, 0);

        $task = Task::create(function () use ($key, $expectedRetries) {
            $shm = SharedMemoryParcel::use(new LocalMutex(), $key);
            $value = $shm->synchronized(fn ($value) => $value + 1);

            if ($value < $expectedRetries) {
                throw new RuntimeException();
            }

            return $value;
        });

        $result = $scheduler->submit($task)->await();
        self::assertSame($expectedRetries, $result);
    }
}
