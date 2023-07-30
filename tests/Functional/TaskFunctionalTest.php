<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Nayleen\Async\Test\TestTask;

/**
 * @internal
 */
final class TaskFunctionalTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_be_serialized(): void
    {
        $task = unserialize(serialize(new TestTask()));
        assert($task instanceof TestTask);

        $task->kernel = TestKernel::create();

        self::assertSame(69, $task->run());
    }

    /**
     * @test
     */
    public function executes_in_kernel_context(): void
    {
        $task = new TestTask();
        $task->kernel = TestKernel::create();

        self::assertSame(69, $task->run());
    }
}
