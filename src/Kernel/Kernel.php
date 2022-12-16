<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel;

use Acclimate\Container\ArrayContainer;
use Acclimate\Container\CompositeContainer;
use Acclimate\Container\ContainerAcclimator;
use Amp\Loop\Driver;
use Nayleen\Async\Kernel\Component\Component;
use Nayleen\Async\Kernel\Component\Components;
use Nayleen\Async\Kernel\Component\DependentComponent;
use Nayleen\Async\Kernel\Exception\ReloadException;
use Nayleen\Async\Kernel\Exception\StopException;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

final class Kernel
{
    private readonly Components $components;

    private ?CompositeContainer $container = null;

    private readonly ContainerAcclimator $containerAcclimator;

    private readonly ?ContainerInterface $wrappedContainer;

    /**
     * @param iterable<class-string<Component>>|Components $components
     */
    public function __construct(
        iterable|Components $components,
        ?ContainerInterface $wrappedContainer = null,
        ?ContainerAcclimator $containerAcclimator = null,
    ) {
        if (is_iterable($components)) {
            $components = new Components(...$components);
        }

        $this->components = $components;
        $this->containerAcclimator = $containerAcclimator ?? new ContainerAcclimator();

        if (isset($wrappedContainer)) {
            $wrappedContainer = $this->containerAcclimator->acclimate($wrappedContainer);
        }

        $this->wrappedContainer = $wrappedContainer;
    }

    public function __destruct()
    {
        $this->shutdown();
        unset($this->container, $this->wrappedContainer);
    }

    public function boot(): ContainerInterface
    {
        if ($this->container !== null) {
            return $this->container;
        }

        $container = new CompositeContainer([
            new ArrayContainer(
                [
                    self::class => $this,
                ],
                $this->wrappedContainer
            )
        ]);

        foreach ($this->components as $component) {
            $providedDependencies = $component->register($container);

            if (isset($providedDependencies)) {
                $providedDependencies = $this->containerAcclimator->acclimate($providedDependencies);
                $container->addContainer($providedDependencies);
            }
        }

        foreach ($this->components as $component) {
            $component->boot($container);
        }

        return $this->container = $container;
    }

    public function reload(Driver $driver): callable
    {
        return static function () use ($driver): void {
            $driver->stop();
            throw new ReloadException();
        };
    }

    public function shutdown(): void
    {
        if ($this->container === null) {
            return;
        }

        foreach (array_reverse($this->components) as $component) {
            $component->shutdown($this->container);
        }

        $this->container = null;
    }

    public function start(?callable $callback = null): void
    {
        reload:
        $container = $this->boot();

        $logger = $container->get(LoggerInterface::class);
        $loop = $container->get(Driver::class);

        if ($callback) {
            $loop->defer($callback);
        }

        try {
            $loop->unreference($loop->defer(static fn () => $logger->debug(sprintf('Loop started using %s.', $loop::class))));
            $loop->run();
        } catch (ReloadException) {
            $this->shutdown();
            goto reload;
        } catch (StopException) {
        }

        $this->shutdown();
    }

    public function stop(Driver $driver): callable
    {
        return static function (string $watcherId) use ($driver): void {
            $driver->cancel($watcherId);
            $driver->stop();
        };
    }
}
