<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Exception;
use LogicException;
use Monolog\Test\TestCase;
use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Component\Components;
use Nayleen\Async\Kernel\Component\DependencyProvider;
use Nayleen\Async\Kernel\Exception\NotRunningException;
use Nayleen\Async\Kernel\Exception\ReloadException;
use Nayleen\Async\Kernel\Exception\StopException;
use Nayleen\Async\Kernel\Runtime\Console;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;

/**
 * @internal
 */
class KernelTest extends TestCase
{
    private function createKernel(
        EventLoop\Driver $loop = null,
        LoggerInterface $logger = null,
        Component ...$components,
    ): Kernel {
        return new Kernel(
            new Components(
                [
                    DependencyProvider::create([
                        EventLoop\Driver::class => $loop ?? EventLoop::getDriver(),
                        LoggerInterface::class => $logger ?? new NullLogger(),
                    ]),
                    ...$components
                ]
            )
        );
    }

    /**
     * @test
     */
    public function booting_multiple_times_returns_same_container(): void
    {
        $kernel = $this->createKernel();

        $container1 = $kernel->boot();
        $container2 = $kernel->boot();

        self::assertSame($container1, $container2);
    }

    /**
     * @test
     */
    public function always_prepends_bootstrapper(): void
    {
        $component = $this->createMock(Component::class);
        $kernel = new Kernel([$component]);

        self::assertEquals(
            [new Bootstrapper(), $component],
            iterator_to_array($kernel->components)
        );
    }

    /**
     * @test
     */
    public function bootstrapper_is_deduplicated(): void
    {
        $bootstrapper = new Bootstrapper();
        $component = $this->createMock(Component::class);
        $kernel = new Kernel([$bootstrapper, $component]);

        self::assertEquals(
            [$bootstrapper, $component],
            iterator_to_array($kernel->components)
        );
    }

    /**
     * @test
     */
    public function components_are_booted_and_shutdown_with_kernel(): void
    {
        $component1 = $this->createMock(Component::class);
        $component1->method('name')->willReturn('component1');
        $component1->expects(self::once())->method('boot');
        $component1->expects(self::once())->method('shutdown');

        $component2 = $this->createMock(Component::class);
        $component2->method('name')->willReturn('component2');
        $component2->expects(self::once())->method('boot');
        $component2->expects(self::once())->method('shutdown');

        $kernel = $this->createKernel(null, null, $component1, $component2);
        $kernel->boot();
        $kernel->shutdown();
    }

    /**
     * @test
     */
    public function run_will_run_event_loop(): void
    {
        $mockDriver = $this->createMock(EventLoop\Driver::class);
        $mockDriver->expects(self::once())->method('run');

        $kernel = $this->createKernel($mockDriver);
        $kernel->run();
    }

    /**
     * @test
     */
    public function start_can_queue_callback_on_loop(): void
    {
        $started = false;
        $this->createKernel()->run(function () use (&$started) {
            $started = true;
        });

        self::assertTrue($started);
    }

    /**
     * @test
     */
    public function started_loop_logs_loop_driver(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('debug')->with(sprintf('Kernel started using %s.', EventLoop::getDriver()::class));

        $kernel = $this->createKernel(logger: $logger);
        $kernel->run();
    }

    /**
     * @test
     */
    public function can_be_reloaded(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('debug')->with(sprintf('Kernel started using %s.', EventLoop::getDriver()::class));

        $invocations = 0;
        $hasBeenReloaded = false;

        $kernel = $this->createKernel(logger: $logger);
        $kernel->run(function (Kernel $kernel) use (&$invocations, &$hasBeenReloaded) {
            // first we trigger a reload
            if ($invocations++ === 0) {
                EventLoop::queue(static fn () => $kernel->reload());
                return;
            }

            // then we stop the loop (otherwise we'd run -> reload -> run ... recursively)
            $hasBeenReloaded = true;
            $kernel->stop();
        });

        self::assertSame(2, $invocations);
        self::assertTrue($hasBeenReloaded);
    }

    /**
     * @test
     */
    public function can_be_reloaded_by_throwing_reload_exception(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('debug')->with(sprintf('Kernel started using %s.', EventLoop::getDriver()::class));

        $invocations = 0;
        $hasBeenReloaded = false;

        $kernel = $this->createKernel(logger: $logger);
        $kernel->run(function (Kernel $kernel) use (&$invocations, &$hasBeenReloaded) {
            // first we trigger a reload
            if ($invocations++ === 0) {
                throw new ReloadException();
            }

            // then we stop the loop (otherwise we'd run -> reload -> run ... recursively)
            $hasBeenReloaded = true;
            $kernel->stop();
        });

        self::assertSame(2, $invocations);
        self::assertTrue($hasBeenReloaded);
    }

    /**
     * @test
     */
    public function can_be_stopped_by_throwing_stop_exception(): void
    {
        $invocations = 0;
        $enteredRun = false;

        $kernel = $this->createKernel();
        $kernel->run(function () use (&$invocations, &$enteredRun) {
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
    public function throws_if_stopped_while_not_running(): void
    {
        $this->expectException(NotRunningException::class);

        $this->createKernel()->stop();
    }

    /**
     * @test
     */
    public function throws_if_failed_while_not_running(): void
    {
        $this->expectException(NotRunningException::class);

        $this->createKernel()->fail($this->createStub(LogicException::class));
    }

    /**
     * @test
     */
    public function failed_kernel_throws_outside_of_loop(): void
    {
        $expectedException = $this->createMock(Exception::class);
        $this->expectExceptionObject($expectedException);

        $kernel = $this->createKernel();
        $kernel->run(function (Kernel $kernel) use ($expectedException) {
            $kernel->fail($expectedException);
        });
    }

    /**
     * @test
     */
    public function can_create_runtimes(): void
    {
        $runtime = $this->createKernel()->create(Console::class);

        self::assertInstanceOf(Console::class, $runtime);
    }
}
