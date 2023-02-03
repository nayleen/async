<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Generator;
use IteratorAggregate;
use Nayleen\Async\Component;
use Nayleen\Finder\Engine\ComposerEngine;
use Nayleen\Finder\Engine\Engine;
use Nayleen\Finder\Expectation\ExtendsClass;
use Nayleen\Finder\Expectation\IsInstantiable;
use Nayleen\Finder\Finder as FinderInterface;
use Traversable;

/**
 * @api
 *
 * @implements FinderInterface<Component>
 * @implements IteratorAggregate<class-string<Component>>
 */
final class Finder implements FinderInterface, IteratorAggregate
{
    /**
     * @var Engine<Component>
     */
    private readonly Engine $engine;

    public function __construct(?Engine $engine = null)
    {
        $engine ??= ComposerEngine::create();

        /**
         * @var Engine<Component> $engine
         */
        $this->engine = $engine;
    }

    /**
     * @return Generator<class-string<Component>>
     */
    public function find(): Generator
    {
        $expectation = (new ExtendsClass(Component::class))->and(new IsInstantiable());

        return $this->engine->find($expectation);
    }

    /**
     * @return Traversable<class-string<Component>>
     */
    public function getIterator(): Traversable
    {
        return $this->find();
    }
}
