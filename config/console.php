<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream\WritableStream;
use DI;
use Nayleen\Async\Console\Command\Finder as CommandFinder;
use Nayleen\Async\Console\Command\Loader as CommandLoader;
use Nayleen\Async\Console\StreamOutput;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\CommandLoader\CommandLoaderInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

return [
    // console services
    Application::class => DI\factory(static function (DI\Container $container): Application {
        $name = sprintf('%s Console', (string) $container->get('async.app_name'));

        $console = new Application($name);
        $console->setAutoExit(false);
        $console->setCommandLoader($container->get(CommandLoaderInterface::class));

        return $console;
    }),

    CommandFinder::class => static fn (): CommandFinder => new CommandFinder(),

    CommandLoader::class => DI\autowire(CommandLoader::class),

    CommandLoaderInterface::class => DI\get(CommandLoader::class),

    InputInterface::class => static fn (): InputInterface => new ArgvInput(),

    OutputInterface::class => DI\factory(static fn (WritableStream $output): OutputInterface => new StreamOutput($output))
        ->parameter('output', DI\get('async.stderr')),
];
