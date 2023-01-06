<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use DI\ContainerBuilder;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Stringable;

abstract class Component implements Stringable
{
    final public function __construct()
    {
    }

    /**
     * @param non-empty-string $configFile
     */
    final protected function configure(ContainerBuilder $containerBuilder, string $configFile): void
    {
        assert(
            file_exists($configFile),
            new InvalidArgumentException(sprintf(
                '%s config file "%s" does not exist!',
                static::class,
                $configFile,
            ))
        );

        /** @var callable(ContainerBuilder, Component): void $configurator */
        $configurator = (require $configFile);
        assert(is_callable($configurator));

        $configurator($containerBuilder);
    }

    public function boot(ContainerInterface $container): void
    {
    }

    /**
     * @return non-empty-string
     */
    abstract public function name(): string;

    abstract public function register(ContainerBuilder $containerBuilder): void;

    public function reload(ContainerInterface $container): void
    {

    }

    public function shutdown(ContainerInterface $container): void
    {
    }

    /**
     * @return non-empty-string
     */
    final public function __toString(): string
    {
        $name = $this->name();
        assert($name !== '');

        return $name;
    }
}
