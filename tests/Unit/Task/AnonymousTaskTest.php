<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class AnonymousTaskTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_be_serialized(): void
    {
        $task = unserialize(serialize(new AnonymousTask(static fn () => 69)));
        assert($task instanceof AnonymousTask);

        $task->kernel = TestKernel::create();

        self::assertSame(69, $task->run());
    }

    /**
     * @test
     */
    public function executes_in_kernel_context(): void
    {
        $task = new AnonymousTask(static fn () => 69);
        $task->kernel = TestKernel::create();

        self::assertSame(69, $task->run());
    }
}
