<?php

declare(strict_types = 1);

namespace Nayleen\Async\Component;

use Amp\PHPUnit\AsyncTestCase;
use Generator;
use Nayleen\Async\Bootstrapper;
use Nayleen\Async\Component;
use Nayleen\Finder\Engine;
use Nayleen\Finder\Expectation;

/**
 * @internal
 * @small
 *
 * @covers \Nayleen\Async\Component\Finder
 */
final class FinderTest extends AsyncTestCase
{
    /**
     * @test
     * @dataProvider provideFinderCases
     *
     * @param class-string<Component>[] $expectedComponents
     */
    public function can_find_components(Engine $engine, array $expectedComponents): void
    {
        $finder = new Finder($engine);

        self::assertSame($expectedComponents, iterator_to_array($finder));
    }

    public function provideFinderCases(): Generator
    {
        $engineMock = new class() implements Engine {
            public function find(Expectation $expectation): iterable
            {
                yield Bootstrapper::class;
            }
        };

        yield 'mocked' => ['engine' => $engineMock, 'expected' => [Bootstrapper::class]];
    }
}
