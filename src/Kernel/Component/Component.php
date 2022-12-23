<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use Nayleen\Async\Kernel\Container\ServiceProvider;
use Psr\Container\ContainerInterface;
use Stringable;

interface Component extends Stringable
{
    public function boot(ContainerInterface $container): void;

    /**
     * @return non-empty-string
     */
    public function name(): string;

    public function register(ServiceProvider $serviceProvider): ContainerInterface;

    public function shutdown(ContainerInterface $container): void;
}
