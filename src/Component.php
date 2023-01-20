<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

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
     * @param non-empty-string $filename
     */
    final protected function load(ContainerBuilder $containerBuilder, string $filename): void
    {
        $definitions = (static function () use ($filename): array {
            assert(
                file_exists($filename) && is_file($filename),
                new InvalidArgumentException(sprintf(
                    '%s config file "%s" does not exist!',
                    static::class,
                    $filename,
                )),
            );

            /**
             * @psalm-suppress UnresolvableInclude
             */
            return (array) require $filename;
        })();

        $containerBuilder->addDefinitions($definitions);
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
        return $this->name();
    }
}
