<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use ArrayIterator;
use DI;
use IteratorAggregate;
use Nayleen\Async\Component\HasDependencies;
use Traversable;

class Components implements IteratorAggregate
{
    use ForbidCloning;
    use ForbidSerialization;

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
            foreach ($component::dependencies() as $dependency) {
                $this->add($dependency);
            }
        }

        return $component;
    }

    private function optimize(DI\ContainerBuilder $containerBuilder): DI\ContainerBuilder
    {
        $tmpContainer = (clone $containerBuilder)->build();

        $debug = (bool) $tmpContainer->get('async.debug');
        $env = $tmpContainer->get('async.env');
        assert(is_string($env) && $env !== '');

        if ($env !== 'prod' || $debug) {
            return $containerBuilder;
        }

        $cacheDir = $tmpContainer->get('async.dir.cache');
        assert(is_string($cacheDir) && file_exists($cacheDir) && is_dir($cacheDir));

        return $containerBuilder->enableCompilation($cacheDir);
    }

    public function boot(Kernel $kernel): void
    {
        foreach ($this->components as $component) {
            $component->boot($kernel);
        }
    }

    public function compile(): DI\Container
    {
        $containerBuilder = new DI\ContainerBuilder();

        foreach ($this->components as $component) {
            $component->register($containerBuilder);
        }

        return $this->optimize($containerBuilder)->build();
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

    public function reload(Kernel $kernel): void
    {
        foreach ($this->components as $component) {
            $component->reload($kernel);
        }
    }

    public function shutdown(Kernel $kernel): void
    {
        foreach (array_reverse($this->components) as $component) {
            $component->shutdown($kernel);
        }
    }
}
