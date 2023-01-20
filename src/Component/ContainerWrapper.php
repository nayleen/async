<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;

abstract class ContainerWrapper extends Component
{
    private readonly ContainerInterface $container;

    /**
     * @return non-empty-string
     */
    private readonly string $name;

    public static function create(ContainerInterface $container): self
    {
        $instance = new class() extends ContainerWrapper {
        };

        /**
         * @psalm-suppress UndefinedPropertyAssignment
         */
        $instance->container = $container;

        /**
         * @psalm-suppress UndefinedPropertyAssignment
         */
        $instance->name = sprintf('container.%s', spl_object_hash($container));

        return $instance;
    }

    /**
     * @return non-empty-string
     */
    public function name(): string
    {
        assert($this->name !== '');

        return $this->name;
    }

    public function register(ContainerBuilder $containerBuilder): void
    {
        $containerBuilder->wrapContainer($this->container);
    }
}
