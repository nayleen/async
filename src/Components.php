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
    private array $components;

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
    private function add(Component|string $component): void
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
    private function make(Component|string $component): Component
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

    public function boot(Kernel $kernel): void
    {
        $runAdvisories = $kernel->container()->get('async.advisories');
        assert(is_bool($runAdvisories));

        foreach ($this->components as $component) {
            if ($runAdvisories) {
                foreach ($component->advisories($kernel) as $advisory) {
                    $advisory->advise($kernel);
                }
            }

            $component->boot($kernel);
        }

        if ($runAdvisories) {
            $kernel->io()->notice('To disable advisories set ASYNC_ADVISORIES to a falsy value like 0');
        }
    }

    public function compile(): DI\Container
    {
        $containerBuilder = new DI\ContainerBuilder();

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
    public function has(Component|string $component): bool
    {
        return isset($this->components[(string) $component]);
    }

    public function shutdown(Kernel $kernel): void
    {
        foreach (array_reverse($this->components) as $component) {
            $component->shutdown($kernel);
        }
    }
}
