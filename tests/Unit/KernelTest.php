<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Future;
use Amp\PHPUnit\AsyncTestCase;
use Nayleen\Async\Test\TestKernel;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

/**
 * @internal
 */
final class KernelTest extends AsyncTestCase
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
    public function run_resolves_future_from_callback(): void
    {
        $return = TestKernel::create()->run(fn () => Future::complete(420));
        self::assertSame(420, $return);
    }

    /**
     * @test
     */
    public function run_returns_value_from_callback(): void
    {
        $return = TestKernel::create()->run(fn () => 420);
        self::assertSame(420, $return);
    }

    /**
     * @test
     */
    public function write_debug_logs_to_debug_logger(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::exactly(2))->method('log');

        $kernel = TestKernel::create()->withDependency('async.logger.debug', $logger);
        $kernel->writeDebug('Testing Kernel');
    }

    /**
     * @test
     */
    public function write_logs_to_default_logger(): void
    {
        $context = [];
        $logLevel = LogLevel::INFO;
        $message = 'Testing Kernel';

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('log')->with($logLevel, $message, $context);

        $kernel = TestKernel::create()->withDependency('async.logger', $logger);
        $kernel->write($logLevel, $message, $context);
    }
}
