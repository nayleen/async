<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use ArrayIterator;
use DI\Container;
use DI\ContainerBuilder;
use IteratorAggregate;
use Psr\Container\ContainerInterface;
use Traversable;

final class Components implements IteratorAggregate
{
    /**
     * @var Component[]
     */
    private array $components = [];

    /**
     * @param iterable<class-string<Component>|Component> $components
     */
    public function __construct(iterable $components = [])
    {
        foreach ($components as $component) {
            $this->add($component);
        }
    }

    /**
     * @param class-string<Component>|Component $component
     */
    private function add(string|Component $component): void
    {
        if ($this->has($component)) {
            return;
        }

        $component = $this->make($component);
        $this->components[$component->name()] = $component;
    }

    /**
     * @param class-string<Component>|Component $component
     */
    private function make(string|Component $component): Component
    {
        if (is_string($component)) {
            $component = new $component();
        }

        if ($component instanceof HasDependencies) {
            foreach ($component->dependencies() as $dependency) {
                $this->add($dependency);
            }
        }

        return $component;
    }

    public function boot(ContainerInterface $container): void
    {
        foreach ($this->components as $component) {
            $component->boot($container);
        }
    }

    public function compile(): Container
    {
        $containerBuilder = new ContainerBuilder();

        foreach ($this->components as $component) {
            $component->register($containerBuilder);
        }

        return $containerBuilder->build();
    }

    /**
     * @return ArrayIterator<array-key, Component>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator(array_values($this->components));
    }

    /**
     * @param class-string<Component>|Component $component
     */
    public function has(string|Component $component): bool
    {
        return isset($this->components[(string) $component]);
    }

    public function reload(ContainerInterface $container): void
    {
        foreach ($this->components as $component) {
            $component->reload($container);
        }
    }

    public function shutdown(ContainerInterface $container): void
    {
        foreach (array_reverse($this->components) as $component) {
            $component->shutdown($container);
        }
    }
}
