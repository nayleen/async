<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Container\Container;
use Nayleen\Async\Kernel\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop\Driver;

/**
 * @internal
 */
final class KernelTestComponent implements Component
{
    public function __construct(
        private readonly Driver $loop,
        private readonly LoggerInterface $logger,
    ) {

    }

    public function boot(ContainerInterface $container): void
    {

    }

    public function name(): string
    {
        return 'test';
    }

    public function register(ServiceProvider $serviceProvider): ContainerInterface
    {
        $container = new Container();
        $container->set(Driver::class, $this->loop);
        $container->set(LoggerInterface::class, $this->logger);

        return $container;
    }

    public function shutdown(ContainerInterface $container): void
    {

    }

    public function __toString(): string
    {
        return $this->name();
    }
}
