<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ByteStream;
use DI;
use Nayleen\Async\Console\Command\Finder;
use Nayleen\Async\Console\StreamOutput;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;

return [
    // console services
    Application::class => static function (DI\Container $container): Application {
        $name = sprintf('%s Console', (string) $container->get('async.app_name'));
        $console = new Application($name);
        $console->setAutoExit(false);

        foreach ((new Finder()) as $class) {
            $command = $container->make($class);
            assert($command instanceof Command);

            $console->add($command);
        }

        return $console;
    },

    OutputInterface::class => static function (ContainerInterface $container): OutputInterface {
        $stream = $container->get('async.stdout');
        assert($stream instanceof ByteStream\WritableResourceStream);

        return new StreamOutput($stream);
    },
];
