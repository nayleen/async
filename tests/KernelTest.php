<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Exception;
use Monolog\Test\TestCase;
use Nayleen\Async\Exception\ReloadException;
use Nayleen\Async\Exception\StopException;
use Nayleen\Async\Test\Kernel as TestKernel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Revolt\EventLoop;

/**
 * @internal
 */
final class KernelTest extends TestCase
{
    /**
     * @test
     */
    public function always_prepends_bootstrapper(): void
    {
        $components = iterator_to_array(TestKernel::create()->components);

        self::assertInstanceOf(Bootstrapper::class, $components[0]);
    }

    /**
     * @test
     */
    public function booting_multiple_times_produces_same_container(): void
    {
        $kernel = TestKernel::create();

        $container1 = $kernel->container();
        $container2 = $kernel->container();

        self::assertSame($container1, $container2);
    }

    /**
     * @test
     */
    public function bootstrapper_gets_deduplicated(): void
    {
        $kernel = new Kernel([new Bootstrapper()]);
        $components = iterator_to_array($kernel->components);

        self::assertInstanceOf(Bootstrapper::class, $components[0]);
        self::assertNotInstanceOf(Bootstrapper::class, $components[1]);
    }

    /**
     * @test
     */
    public function can_be_reloaded(): void
    {
        $invocations = 0;
        $hasBeenReloaded = false;

        TestKernel::create()->run(function (Kernel $kernel) use (&$invocations, &$hasBeenReloaded): void {
            // first we trigger a reload
            if ($invocations++ === 0) {
                $kernel->reload();
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
    public function can_be_reloaded_by_throwing_reload_exception(): void
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

        $return = TestKernel::create()->run(function (Kernel $kernel) use (&$invocations, &$enteredRun): void {
            $enteredRun = true;
            $invocations++;

            $kernel->stop();
        });

        self::assertNull($return);
        self::assertSame(1, $invocations);
        self::assertTrue($enteredRun);
    }

    /**
     * @test
     */
    public function can_be_stopped_by_throwing_stop_exception(): void
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
    public function can_be_stopped_by_throwing_stop_exception_with_signal(): void
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

    /**
     * @test
     */
    public function can_be_stopped_with_signal(): void
    {
        $invocations = 0;
        $enteredRun = false;

        $return = TestKernel::create()->run(function (Kernel $kernel) use (&$invocations, &$enteredRun): void {
            $enteredRun = true;
            $invocations++;

            $kernel->stop(SIGINT);
        });

        self::assertSame($return, SIGINT);
        self::assertSame(1, $invocations);
        self::assertTrue($enteredRun);
    }

    /**
     * @test
     */
    public function failed_kernel_throws_outside_of_loop(): void
    {
        $expectedException = $this->createMock(Exception::class);
        $this->expectExceptionObject($expectedException);

        TestKernel::create()->run(fn () => throw $expectedException);
    }

    /**
     * @test
     */
    public function run_can_queue_callback_on_loop(): void
    {
        $started = false;
        TestKernel::create()->run(function () use (&$started): void {
            $started = true;
        });

        self::assertTrue($started);
    }

    /**
     * @test
     */
    public function run_will_run_event_loop(): void
    {
        $mockDriver = $this->createMock(EventLoop\Driver::class);
        $mockDriver->expects(self::once())->method('run');

        $kernel = TestKernel::create(loop: $mockDriver);
        $kernel->run(fn () => null);
    }

    /**
     * @test
     */
    public function write_debug_logs_to_stderr(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('log');

        $kernel = TestKernel::create(stdErrLogger: $logger);
        $kernel->writeDebug('Testing Kernel');
    }

    /**
     * @test
     */
    public function write_logs_to_stdout(): void
    {
        $context = [];
        $logLevel = LogLevel::INFO;
        $message = 'Testing Kernel';

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('log')->with($logLevel, $message, $context);

        $kernel = TestKernel::create(stdOutLogger: $logger);
        $kernel->write($logLevel, $message, $context);
    }
}
