<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use Generator;
use IteratorAggregate;
use Nayleen\Finder\Engine\ComposerEngine;
use Nayleen\Finder\Engine\Engine;
use Nayleen\Finder\Expectation\ExtendsClass;
use Nayleen\Finder\Finder as FinderInterface;
use Traversable;

/**
 * @template-extends FinderInterface
 */
final class Finder implements FinderInterface, IteratorAggregate
{
    private readonly Engine $engine;

    public function __construct(?Engine $engine = null)
    {
        $this->engine = $engine ?? ComposerEngine::create();
    }

    public function find(): Generator
    {
        return $this->engine->find(new ExtendsClass(Component::class));
    }

    public function getIterator(): Traversable
    {
        return $this->find();
    }
}
