<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\ByteStream\WritableBuffer;
use Amp\CancelledException;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Kernel;
use Nayleen\Async\Task;
use Nayleen\Async\Test\AmpTask;
use Nayleen\Async\Test\TestKernel;
use Nayleen\Async\Test\TestTask;

use function Amp\delay;

/**
 * @internal
 * @large
 */
final class SchedulerFunctionalTest extends AsyncTestCase
{
    private function createScheduler(?TestKernel $kernel = null): Scheduler
    {
        return new Scheduler($kernel ?? new TestKernel());
    }

    /**
     * @test
     */
    public function can_run_amp_task(): void
    {
        $scheduler = $this->createScheduler();
        self::assertSame(42, $scheduler->run(new AmpTask()));
    }

    /**
     * @test
     */
    public function can_run_closure(): void
    {
        $scheduler = $this->createScheduler();
        self::assertSame(69, $scheduler->run(static fn () => 69));
    }

    /**
     * @test
     */
    public function can_run_script(): void
    {
        $scheduler = $this->createScheduler();
        self::assertSame(69, $scheduler->run(dirname(__DIR__, 3) . '/src/Test/nice-script.php'));
    }

    /**
     * @test
     */
    public function can_run_task(): void
    {
        $scheduler = $this->createScheduler();
        self::assertSame(69, $scheduler->run(new TestTask()));
    }

    /**
     * @test
     */
    public function can_timeout_long_running_task(): void
    {
        $this->expectException(CancelledException::class);

        $scheduler = $this->createScheduler();
        self::assertNull($scheduler->run(static fn () => delay(1), 0.0));
    }

    /**
     * @test
     */
    public function pipes_stderr_from_child(): void
    {
        $stdErr = new WritableBuffer();

        $scheduler = $this->createScheduler(new TestKernel(stdErr: $stdErr));
        $task = new Task(static fn (Kernel $kernel) => $kernel->io()->debug('Child says uhoh!'));

        $scheduler->run($task);
        delay(0.001);

        $stdErr->close();
        self::assertStringContainsString('Child says uhoh!', $stdErr->buffer());
    }

    /**
     * @test
     */
    public function resubmitting_cancels_previous_execution(): void
    {
        $delay = 0.001;
        $scheduler = $this->createScheduler();

        $task = new Task(static function () use ($delay) {
            $result = 0;
            while ($result < 400) {
                delay($delay);
                $result += 69;
            }

            return $result + 6;
        });

        $scheduler->submit($task);
        delay($delay);

        $result = $scheduler->run($task);
        self::assertSame(420, $result);
    }
}
