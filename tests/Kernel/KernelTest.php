<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Monolog\Test\TestCase;
use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Component\Components;
use Nayleen\Async\Kernel\Container\Container;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;
use Revolt\EventLoop\DriverFactory;
use stdClass;

/**
 * @internal
 */
class KernelTest extends TestCase
{
    private LoggerInterface|MockObject $logger;

    private EventLoop\Driver|MockObject $loop;

    private function createKernel(
        EventLoop\Driver $loop = null,
        LoggerInterface $logger = null,
        Component ...$components,
    ): Kernel {
        $this->logger = $logger ?? new NullLogger();
        $this->loop = $loop ?? EventLoop::getDriver();

        $container = new Container();

        $kernelComponents = new Components($container);
        foreach ([new KernelTestComponent($this->loop, $this->logger), ...$components] as $component) {
            $kernelComponents->add($component);
        }

        return Kernel::create($kernelComponents, $container);
    }

    /**
     * @test
     */
    public function boot_creates_container(): void
    {
        $kernel = $this->createKernel();

        self::assertInstanceOf(ContainerInterface::class, $kernel->boot());
    }

    /**
     * @test
     */
    public function allows_access_to_components(): void
    {
        $component = $this->createMock(Component::class);
        $kernel = $this->createKernel(components: $component);

        self::assertEquals(
            [new KernelTestComponent($this->loop, $this->logger), $component],
            iterator_to_array($kernel->components())
        );
    }

    /**
     * @test
     */
    public function produced_container_can_wrap_a_top_level_container(): void
    {
        $wrappedContainer = new Container();
        $wrappedContainer->set(stdClass::class, $instance = new stdClass());

        $kernel = Kernel::create([], $wrappedContainer);
        $container = $kernel->boot();

        self::assertTrue($container->has(stdClass::class));
        self::assertSame($instance, $container->get(stdClass::class));
    }

    /**
     * @test
     */
    public function components_are_booted_in_correct_order(): void
    {
        $bootOrder = [];
        $shutdownOrder = [];

        $component1 = $this->createMock(Component::class);
        $component1->method('__toString')->willReturn('component1');
        $component1->expects(self::once())->method('boot')->willReturnCallback(function () use (&$bootOrder) {
            $bootOrder[] = 'component1';
        });
        $component1->expects(self::once())->method('shutdown')->willReturnCallback(function () use (&$shutdownOrder) {
            $shutdownOrder[] = 'component1';
        });

        $component2 = $this->createMock(Component::class);
        $component2->method('__toString')->willReturn('component2');
        $component2->expects(self::once())->method('boot')->willReturnCallback(function () use (&$bootOrder) {
            $bootOrder[] = 'component2';
        });
        $component2->expects(self::once())->method('shutdown')->willReturnCallback(function () use (&$shutdownOrder) {
            $shutdownOrder[] = 'component2';
        });

        $kernel = $this->createKernel(null, null, $component1, $component2);

        $kernel->boot();
        $kernel->boot(); // repeat boots do not create the container again
        self::assertSame(['component1', 'component2'], $bootOrder);

        $kernel->shutdown();
        $kernel->shutdown();
        self::assertSame(['component2', 'component1'], $shutdownOrder);
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
    public function started_loop_informs_about_driver_via_logger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('debug')->with(sprintf('Loop started using %s.', EventLoop::getDriver()::class));

        $kernel = $this->createKernel(logger: $logger);
        $kernel->run();
    }

    /**
     * @test
     * @depends started_loop_informs_about_driver_via_logger
     */
    public function kernel_can_be_reloaded_via_cancellation(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('debug')->with(sprintf('Loop started using %s.', EventLoop::getDriver()::class));

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
}
