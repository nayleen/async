<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command;

use Generator;
use Nayleen\Async\Component\Finder;
use Nayleen\Finder\Engine\Engine;
use Nayleen\Finder\Expectation\Expectation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\HelpCommand;

/**
 * @internal
 */
class FinderTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideFinderCases
     */
    public function can_find_components(Engine $engine, array $expectedCommands): void
    {
        $finder = new Finder($engine);

        self::assertSame($expectedCommands, iterator_to_array($finder));
    }

    public function provideFinderCases(): Generator
    {
        $engineMock = new class() implements Engine {
            public function find(Expectation $expectation): Generator
            {
                yield HelpCommand::class;
            }
        };

        yield 'mocked' => ['engine' => $engineMock, 'expected' => [HelpCommand::class]];
    }
}
