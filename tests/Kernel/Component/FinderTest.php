<?php

declare(strict_types = 1);

namespace Nayleen\Async\Kernel\Component;

use Generator;
use Nayleen\Async\Kernel\Bootstrapper;
use Nayleen\Finder\Engine\ComposerEngine;
use Nayleen\Finder\Engine\Engine;
use Nayleen\Finder\Expectation\Expectation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class FinderTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideFinderCases
     */
    public function can_find_components(Engine $engine, array $expectedComponents): void
    {
        $finder = new Finder($engine);

        self::assertSame($expectedComponents, iterator_to_array($finder));
    }

    public function provideFinderCases(): Generator
    {
        $engineMock = new class implements Engine {
            public function find(Expectation $expectation): Generator
            {
                yield Bootstrapper::class;
            }
        };

        yield 'mocked' => ['engine' => $engineMock, 'expected' => [Bootstrapper::class]];
        yield 'composer' => ['engine' => ComposerEngine::create(), 'expected' => []];
    }
}
