<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command;

use Generator;
use IteratorAggregate;
use Nayleen\Finder\Engine\ComposerEngine;
use Nayleen\Finder\Engine\Engine;
use Nayleen\Finder\Expectation\ExtendsClass;
use Nayleen\Finder\Expectation\IsInstantiable;
use Nayleen\Finder\Finder as FinderInterface;
use Symfony\Component\Console\Command\Command;
use Traversable;

/**
 * @template-implements FinderInterface<Command>
 * @template-implements IteratorAggregate<class-string<Command>>
 */
class Finder implements FinderInterface, IteratorAggregate
{
    /**
     * @var Engine<Command>
     */
    private readonly Engine $engine;

    public function __construct(?Engine $engine = null)
    {
        $engine ??= ComposerEngine::create();

        /**
         * @var Engine<Command> $engine
         */
        $this->engine = $engine;
    }

    public function find(): Generator
    {
        $expectation = (new ExtendsClass(Command::class))->and(new IsInstantiable());

        return $this->engine->find($expectation);
    }

    public function getIterator(): Traversable
    {
        return $this->find();
    }
}
