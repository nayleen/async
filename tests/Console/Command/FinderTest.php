<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command;

use Generator;
use Nayleen\Finder\Engine\Engine;
use Nayleen\Finder\Expectation\Expectation;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

/**
 * @internal
 */
class FinderTest extends TestCase
{
    /**
     * @test
     * @dataProvider provideFinderCases
     *
     * @param class-string<Command>[] $expectedCommands
     */
    public function can_find_commands(Engine $engine, array $expectedCommands): void
    {
        $finder = new Finder($engine);

        self::assertSame($expectedCommands, iterator_to_array($finder));
    }

    public function provideFinderCases(): Generator
    {
        $engineMock = new class() implements Engine {
            public function find(Expectation $expectation): Generator
            {
                yield Command::class;
            }
        };

        yield 'mocked' => ['engine' => $engineMock, 'expected' => [Command::class]];
    }
}
