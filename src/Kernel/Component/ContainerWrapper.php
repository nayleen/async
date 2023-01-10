<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

abstract class ContainerWrapper extends Component
{
    private readonly ContainerInterface $container;

    private readonly string $name;

    public static function create(ContainerInterface $container): self
    {
        $instance = new class extends ContainerWrapper {};
        $instance->container = $container;
        $instance->name = sprintf('container.%s', spl_object_hash($container));

        return $instance;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->wrapContainer($this->container);
    }
}
