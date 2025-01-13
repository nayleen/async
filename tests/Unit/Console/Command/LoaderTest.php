<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command;

use Amp\PHPUnit\AsyncTestCase;
use DI\Container;
use Nayleen\Finder\Engine;
use Nayleen\Finder\Expectation;
use Symfony\Component\Console\Command\ListCommand;
use Symfony\Component\Console\Exception\CommandNotFoundException;

/**
 * @internal
 * @small
 */
final class LoaderTest extends AsyncTestCase
{
    /**
     * @test
     */
    public function can_list_names_of_loadable_commands(): void
    {
        $loader = new Loader(
            new Finder(new class() implements Engine {
                public function find(Expectation $expectation): iterable
                {
                    yield ListCommand::class;
                }
            }),
            new Container(),
        );

        self::assertSame(['list'], $loader->getNames());
    }

    /**
     * @test
     */
    public function finds_command(): void
    {
        $loader = new Loader(
            new Finder(new class() implements Engine {
                public function find(Expectation $expectation): iterable
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
