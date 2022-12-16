<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

final class Components implements Countable, IteratorAggregate
{
    /**
     * @var Component[]
     */
    private array $components = [];

    public function __construct(Component ...$components)
    {
        foreach ($components as $component) {
            $this->add($component);
        }
    }

    private function register(Component $component): void
    {
        if (!$component instanceof DependentComponent) {
            return;
        }

        foreach ($component->dependencies() as $dependency) {
            $this->add(new $dependency());
        }
    }

    public function add(Component $component): void
    {
        if ($this->has($component)) {
            return;
        }

        $this->register($component);
        $this->components[(string) $component] = $component;
    }

    public function count(): int
    {
        return count($this->components);
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
}
