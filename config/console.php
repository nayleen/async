<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Sync\Channel;
use DI;
use Nayleen\Async\Console\Channel\Output;
use Nayleen\Async\Console\Command\Finder;
use Nayleen\Async\Console\Command\Finder as CommandFinder;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    // console services
    Application::class => DI\factory(static function (DI\Container $container): Application {
        $name = sprintf('%s Console', (string) $container->get('async.app_name'));
        $console = new Application($name);
        $console->setAutoExit(false);

        foreach ($container->get(CommandFinder::class) as $class) {
            $command = $container->make($class);
            assert($command instanceof Command);

            $console->add($command);
        }

        return $console;
    }),

    CommandFinder::class => static fn (): Finder => new Finder(),

    InputInterface::class => static fn (): InputInterface => new ArgvInput(),

    OutputInterface::class => static fn (Channel $channel): Output => new Output($channel),
];
