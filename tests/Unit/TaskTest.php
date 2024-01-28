<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use AssertionError;
use Nayleen\Async\Task\AnonymousTask;

/**
 * @internal
 */
final class TaskTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function from_callable_throws_on_invalid_first_parameter(): void
    {
        $this->expectException(AssertionError::class);

        new AnonymousTask(static fn ($a): mixed => null);
    }

    /**
     * @test
     */
    public function from_script_throws_on_invalid_script(): void
    {
        $this->expectException(AssertionError::class);

        AnonymousTask::fromScript(dirname(__DIR__, 2) . '/config/async.php');
    }

    /**
     * @test
     */
    public function from_script_throws_on_non_existent_script(): void
    {
        $this->expectException(AssertionError::class);

        AnonymousTask::fromScript(__DIR__ . '/non-existent-script.php');
    }
}
