<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use AssertionError;
use Nayleen\Async\Test\TestKernel;
use Nayleen\Async\Test\TestTask;

/**
 * @internal
 */
final class TaskTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_be_serialized(): void
    {
        $task = unserialize(serialize(new Task(static fn () => 69)));
        assert($task instanceof Task);

        self::assertSame(69, $task->execute(TestKernel::create()));
    }

    /**
     * @test
     */
    public function executes_in_kernel_context(): void
    {
        $task = new TestTask();

        self::assertSame(69, $task->execute(TestKernel::create()));
    }

    /**
     * @test
     */
    public function from_callable_throws_on_invalid_first_parameter(): void
    {
        $this->expectException(AssertionError::class);

        new Task(static fn ($a): mixed => null);
    }

    /**
     * @test
     */
    public function from_script_throws_on_invalid_script(): void
    {
        $this->expectException(AssertionError::class);

        Task::fromScript(dirname(__DIR__, 2) . '/config/async.php');
    }

    /**
     * @test
     */
    public function from_script_throws_on_non_existent_script(): void
    {
        $this->expectException(AssertionError::class);

        Task::fromScript(__DIR__ . '/non-existent-script.php');
    }
}
