<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Amp\DeferredCancellation;
use Monolog\Test\TestCase;
use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Component\Components;
use Nayleen\Async\Kernel\Container\Container;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Revolt\EventLoop;
use Revolt\EventLoop\Driver;

/**
 * @internal
 */
class KernelTest extends TestCase
{
    private function createKernel(Component ...$components): Kernel
    {
        $container = new Container();
        $container->set(LoggerInterface::class, new NullLogger());

        $kernelComponents = new Components($container);
        foreach ($components as $component) {
            $kernelComponents->add($component);
        }

        return new Kernel($kernelComponents, $container);
    }

    /**
     * @test
     */
    public function boot_creates_container(): void
    {
        $kernel = $this->createKernel();
        $container = $kernel->boot();

        self::assertInstanceOf(ContainerInterface::class, $container);
    }

    /**
     * @test
     */
    public function allows_access_to_components(): void
    {
        $component = $this->createMock(Component::class);
        $kernel = $this->createKernel($component);

        self::assertEquals([$component], iterator_to_array($kernel->components()));
    }

    /**
     * @test
     */
    public function produced_container_can_wrap_a_top_level_container(): void
    {
        $wrappedContainer = new Container();
        $wrappedContainer->set(LoggerInterface::class, $logger = $this->createStub(LoggerInterface::class));

        $kernel = Kernel::create([], $wrappedContainer);
        $container = $kernel->boot();

        self::assertTrue($container->has(LoggerInterface::class));
        self::assertSame($logger, $container->get(LoggerInterface::class));
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

        $kernel = $this->createKernel($component1, $component2);

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
    public function start_will_run_event_loop(): void
    {
        $mockDriver = $this->createMock(Driver::class);
        $mockDriver->expects(self::once())->method('queue');
        $mockDriver->expects(self::once())->method('run');

        $wrappedContainer = new Container();
        $wrappedContainer->set(Driver::class, $mockDriver);
        $wrappedContainer->set(LoggerInterface::class, new NullLogger());

        $kernel = Kernel::create([], $wrappedContainer);
        $kernel->run();
    }

    /**
     * @test
     */
    public function start_can_queue_callback_on_loop(): void
    {
        $loopDriver = EventLoop::getDriver();

        $wrappedContainer = new Container();
        $wrappedContainer->set(Driver::class, $loopDriver);
        $wrappedContainer->set(LoggerInterface::class, new NullLogger());

        $started = false;

        $kernel = Kernel::create([], $wrappedContainer);
        $kernel->run(function (Driver $loop) use (&$started) {
            $started = true;
            $loop->stop();
        });

        self::assertTrue($started);
    }

    /**
     * @test
     */
    public function started_loop_informs_about_driver_via_logger(): void
    {
        $loop = EventLoop::getDriver();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('debug')->with(sprintf('Loop started using %s.', $loop::class));

        $wrappedContainer = new Container();
        $wrappedContainer->set(Driver::class, $loop);
        $wrappedContainer->set(LoggerInterface::class, $logger);

        $kernel = Kernel::create([], $wrappedContainer);
        $kernel->run();
    }

    /**
     * @test
     * @depends started_loop_informs_about_driver_via_logger
     */
    public function kernel_can_be_reloaded_via_cancellation(): void
    {
        $loop = EventLoop::getDriver();

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('debug')->with(sprintf('Loop started using %s.', $loop::class));

        $wrappedContainer = new Container();
        $wrappedContainer->set(Driver::class, $loop);
        $wrappedContainer->set(LoggerInterface::class, $logger);

        $hasBeenReloaded = false;

        $reload = new DeferredCancellation();
        $stop = new DeferredCancellation();
        $reload->getCancellation()->subscribe(function () use (&$hasBeenReloaded) {
            $hasBeenReloaded = true;
        });

        $kernel = Kernel::create([], $wrappedContainer);
        $kernel->reloadOn($reload->getCancellation());
        $kernel->run(function () use ($loop, $reload, $stop) {
            // first we trigger a reload
            $loop->defer(fn () => $reload->cancel());
            // then we stop the loop (otherwise we'd run -> reload -> run ... recursively)
            //$loop->defer(fn () => $stop->cancel());
        });

        self::assertTrue($hasBeenReloaded);
    }
}
