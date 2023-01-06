<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use LogicException;
use Monolog\Test\TestCase;
use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Component\Components;
use Nayleen\Async\Runtime\Runtime;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;
use function Amp\delay;

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
                    KernelTestComponent::create(
                        $loop ?? EventLoop::getDriver(),
                        $logger ?? new NullLogger(),
                    ),
                    ...$components
                ]
            )
        );
    }

    /**
     * @test
     */
    public function boot_creates_container(): void
    {
        self::assertInstanceOf(ContainerInterface::class, $this->createKernel()->boot());
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
    public function create_prepends_bootstrapper(): void
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
    public function bootstrapper_is_deduplicated_if_auto_prepended(): void
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
        $mockDriver->expects(self::once())->method('queue');
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

        $kernel = $this->createKernel();
        $kernel->run(function (Kernel $kernel) use (&$started) {
            $started = true;
            $kernel->stop();
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
    public function kernel_can_be_reloaded(): void
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
            EventLoop::queue(static fn () => $kernel->stop());
        });

        self::assertSame(2, $invocations);
        self::assertTrue($hasBeenReloaded);
    }

    /**
     * @test
     */
    public function throws_if_stopped_while_not_running(): void
    {
        $this->expectException(LogicException::class);

        $kernel = $this->createKernel();
        $kernel->stop();
    }
}
