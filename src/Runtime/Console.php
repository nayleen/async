<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Console implements Runtime
{
    public function __construct(private readonly Application $console)
    {
        $this->console->setAutoExit(false);
    }

    public function run(
        string|Command $command = null,
        ?InputInterface $input = null,
        ?OutputInterface $output = null,
    ): int {
        if ($command) {
            $command = $command instanceof Command
                ? $command->getName()
                : $command;

            $this->console->setDefaultCommand($command, true);
        }

        return $this->console->run($input, $output);
    }
}
