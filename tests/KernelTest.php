<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Sync\Channel;
use Exception;
use Monolog\Test\TestCase;
use Nayleen\Async\Component\DependencyProvider;
use Nayleen\Async\Exception\ReloadException;
use Nayleen\Async\Exception\StopException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;
use Revolt\EventLoop;

/**
 * @internal
 */
class KernelTest extends TestCase
{
    private function createComponents(
        ?EventLoop\Driver $loop = null,
        ?LoggerInterface $stdErrLogger = null,
        ?LoggerInterface $stdOutLogger = null,
        Component ...$components,
    ): Components {
        return new Components(
            [
                DependencyProvider::create([
                    EventLoop\Driver::class => $loop ?? EventLoop::getDriver(),
                    'async.logger.stderr' => $stdErrLogger ?? new NullLogger(),
                    'async.logger.stdout' => $stdOutLogger ?? new NullLogger(),
                ]),
                ...$components,
            ],
        );
    }

    private function createKernel(
        ?EventLoop\Driver $loop = null,
        ?LoggerInterface $stdErrLogger = null,
        ?LoggerInterface $stdOutLogger = null,
        Component ...$components,
    ): Kernel {
        return new Kernel($this->createComponents($loop, $stdErrLogger, $stdOutLogger, ...$components));
    }

    /**
     * @test
     */
    public function always_prepends_bootstrapper(): void
    {
        $kernel = new Kernel();

        $components = (fn () => $this->components)->bindTo($kernel, $kernel)();
        $components = iterator_to_array($components);

        self::assertInstanceOf(Bootstrapper::class, $components[0]);
    }

    /**
     * @test
     */
    public function booting_multiple_times_produces_same_container(): void
    {
        $kernel = new Kernel();

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

        $components = (fn () => $this->components)->bindTo($kernel, $kernel)();
        $components = iterator_to_array($components);

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

        $this->createKernel()->run(function () use (&$invocations, &$hasBeenReloaded): void {
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

        $this->createKernel()->run(function () use (&$invocations, &$enteredRun): void {
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
    public function can_be_stopped_by_throwing_stop_exception(): void
    {
        $invocations = 0;
        $enteredRun = false;

        $kernel = $this->createKernel();
        $kernel->run(function () use (&$invocations, &$enteredRun): void {
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
    public function failed_kernel_throws_outside_of_loop(): void
    {
        $expectedException = $this->createMock(Exception::class);
        $this->expectExceptionObject($expectedException);

        $this->createKernel()->run(fn () => throw $expectedException);
    }

    /**
     * @test
     */
    public function run_can_queue_callback_on_loop(): void
    {
        $started = false;
        $this->createKernel()->run(function () use (&$started): void {
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

        $kernel = $this->createKernel($mockDriver);
        $kernel->run(fn () => null);
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

        $kernel = $this->createKernel(stdOutLogger: $logger);
        $kernel->write($logLevel, $message, $context);
    }
}
