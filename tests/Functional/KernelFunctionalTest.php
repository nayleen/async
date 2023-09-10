<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Exception\ReloadException;
use Nayleen\Async\Exception\StopException;
use Nayleen\Async\Test\TestKernel;

/**
 * @internal
 */
final class KernelFunctionalTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_be_reloaded(): void
    {
        $invocations = 0;
        $hasBeenReloaded = false;

        TestKernel::create()->run(function () use (&$invocations, &$hasBeenReloaded): void {
            // first we trigger a reload
            if ($invocations++ === 0) {
                throw new ReloadException();
            }

            // then we stop the loop (otherwise we'd run -> reload -> run ... recursively)
            $hasBeenReloaded = true;
        });

        self::assertSame(2, $invocations);
        self::assertTrue($hasBeenReloaded);
    }

    /**
     * @test
     */
    public function can_be_stopped(): void
    {
        $invocations = 0;
        $enteredRun = false;

        TestKernel::create()->run(function () use (&$invocations, &$enteredRun): void {
            $enteredRun = true;
            $invocations++;

            throw new StopException();
        });

        self::assertSame(1, $invocations);
        self::assertTrue($enteredRun);
    }

    /**
     * @test
     */
    public function can_be_stopped_with_signal(): void
    {
        $invocations = 0;
        $enteredRun = false;

        $return = TestKernel::create()->run(function () use (&$invocations, &$enteredRun): void {
            $enteredRun = true;
            $invocations++;

            throw new StopException(SIGINT);
        });

        self::assertSame($return, SIGINT);
        self::assertSame(1, $invocations);
        self::assertTrue($enteredRun);
    }
}
