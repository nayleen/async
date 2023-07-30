<?php

declare(strict_types = 1);

namespace Nayleen\Async\Worker;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Nayleen\Async\Worker;

/**
 * @internal
 */
final class TaskTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function resolves_and_runs_worker(): void
    {
        $worker = $this->createMock(Worker::class);
        $worker->expects(self::once())->method('run');

        $task = new Task(Worker::class);
        $task->kernel = TestKernel::create()->withDependency(Worker::class, $worker);
        $task->run();
    }
}
