<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Task as TaskInterface;
use AssertionError;
use Closure;
use Nayleen\Async\Test\AmpTask;
use Nayleen\Async\Test\RuntimeTestCase;
use Nayleen\Async\Test\TestTask;

/**
 * @internal
 * @small
 *
 * @covers \Nayleen\Async\Runtime
 * @covers \Nayleen\Async\Task
 * @covers \Nayleen\Async\Test\AmpTask
 * @covers \Nayleen\Async\Test\TestTask
 */
final class TaskTest extends RuntimeTestCase
{
    /**
     * @return iterable<string, array{0: Closure|string|TaskInterface, 1: int}>
     */
    public static function provideTask(): iterable
    {
        yield 'closure' => [static fn () => 1337, 1337];
        yield 'instance' => [new TestTask(), 69];
        yield 'script' => [dirname(__DIR__, 2) . '/src/Test/nice-script.php', 69];
        yield 'task interface' => [new AmpTask(), 42];
    }

    /**
     * @test
     */
    public function can_be_serialized(): void
    {
        $task = unserialize(serialize(new Task(static fn () => 69)));

        self::assertInstanceOf(Task::class, $task);
    }

    /**
     * @test
     * @dataProvider provideTask
     */
    public function executes_in_kernel_context(Closure|string|TaskInterface $task, int $expectedReturn): void
    {
        self::assertSame($expectedReturn, $this->execute(Task::create($task)));
    }

    /**
     * @test
     */
    public function from_callable_throws_on_invalid_first_parameter(): void
    {
        $this->expectException(AssertionError::class);

        new Task(static fn ($a) => null);
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
