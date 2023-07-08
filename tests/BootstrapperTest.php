<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Log\ConsoleFormatter;
use Amp\Log\StreamHandler;
use DI\ContainerBuilder;
use Monolog\Logger;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;

/**
 * @internal
 */
final class BootstrapperTest extends TestCase
{
    /**
     * @test
     */
    public function populates_parameters_with_environment_values(): void
    {
        $bootstrapper = new Bootstrapper();
        $bootstrapper->register($containerBuilder = new ContainerBuilder());

        $container = $containerBuilder->build();

        self::assertTrue($container->get('async.debug'));
        self::assertSame('/usr/src/app', $container->get('async.dir.base'));
        self::assertSame('/tmp', $container->get('async.dir.cache'));
        self::assertSame('test', $container->get('async.env'));
    }

    /**
     * @test
     */
    public function registers_default_logger(): void
    {
        $bootstrapper = new Bootstrapper();
        $bootstrapper->register($containerBuilder = new ContainerBuilder());

        $container = $containerBuilder->build();

        $logger = $container->get(Logger::class);

        // is aliased correctly
        self::assertSame($container->get(LoggerInterface::class), $logger);

        // uses the async stream handler
        $handlers = $logger->getHandlers();
        self::assertCount(1, $handlers);

        $handler = $handlers[0];
        self::assertInstanceOf(StreamHandler::class, $handler);

        // uses the async console formatter
        $formatter = $handler->getFormatter();
        self::assertInstanceOf(ConsoleFormatter::class, $formatter);
    }

    /**
     * @test
     */
    public function registers_default_loop_driver(): void
    {
        $bootstrapper = new Bootstrapper();
        $bootstrapper->register($containerBuilder = new ContainerBuilder());

        $container = $containerBuilder->build();

        $driver = $container->get(EventLoop\Driver::class);
        self::assertInstanceOf(EventLoop\Driver\TracingDriver::class, $driver);
    }

    /**
     * @test
     */
    public function sets_container_builder_defaults(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);
        $containerBuilder->expects(self::once())->method('useAttributes')->with(false);
        $containerBuilder->expects(self::once())->method('useAutowiring')->with(true);

        $bootstrapper = new Bootstrapper();
        $bootstrapper->register($containerBuilder);
    }
}
