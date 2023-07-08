<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use DI\ContainerBuilder;
use Nayleen\Async\Component;
use Psr\Container\ContainerInterface;

/**
 * @api
 */
final class ContainerWrapper extends Component
{
    private ContainerInterface $container;

    /**
     * @return non-empty-string
     */
    private string $name;

    public static function create(ContainerInterface $container): self
    {
        $instance = new self();
        $instance->container = $container;
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
