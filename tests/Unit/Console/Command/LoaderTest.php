<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command;

use Amp\PHPUnit\AsyncTestCase;
use DI\Container;
use Generator;
use Nayleen\Finder\Engine\Engine;
use Nayleen\Finder\Expectation\Expectation;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * @internal
 */
final class LoaderTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function finds_command(): void
    {
        $loader = new Loader(
            new Finder(new class() implements Engine {
                public function find(Expectation $expectation): Generator
                {
                    yield ListCommand::class;
                }
            }),
            new Container(),
        );

        self::assertInstanceOf(ListCommand::class, $loader->get('list'));
    }

    /**
     * @test
     */
    public function throws_on_unknown_command(): void
    {
        $this->expectException(CommandNotFoundException::class);

        $loader = new Loader(new Finder(), new Container());
        $loader->get('list');
    }
}
