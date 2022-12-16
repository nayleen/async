<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use Psr\Container\ContainerInterface;

interface Component
{
    public function boot(ContainerInterface $container): void;

    /**
     * @return non-empty-string
     */
    public function name(): string;

    public function register(ContainerInterface $container): ?ContainerInterface;

    public function shutdown(ContainerInterface $container): void;
}
