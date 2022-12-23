<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use Generator;
use Nayleen\Finder\Engine\ComposerEngine;
use Nayleen\Finder\Engine\Engine;
use Nayleen\Finder\Expectation\ImplementsInterface;
use Nayleen\Finder\Finder as FinderInterface;

/**
 * @template-extends FinderInterface
 */
final class Finder implements FinderInterface
{
    private readonly Engine $engine;

    public function __construct(?Engine $engine = null)
    {
        $this->engine = $engine ?? ComposerEngine::create();
    }

    public function find(): Generator
    {
        return $this->engine->find(new ImplementsInterface(Component::class));
    }
}
