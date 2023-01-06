<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Kernel\Kernel;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Console extends Runtime
{
    private ?InputInterface $input = null;
    private ?OutputInterface $output = null;

    public function __construct(
        Kernel $kernel,
        private readonly Application $console,
    ) {
        parent::__construct($kernel);

        $this->console->setAutoExit(false);
    }

    protected function execute(): int
    {
        return $this->console->run($this->input, $this->output);
    }

    public function command(string|Command $command): self
    {
        if ($command instanceof Command) {
            $command = (string) $command->getName();
        }

        assert($command !== '');
        $this->console->setDefaultCommand($command, true);

        return $this;
    }

    public function input(InputInterface $input): self
    {
        $this->input = $input;

        return $this;
    }

    public function output(OutputInterface $output): self
    {
        $this->output = $output;

        return $this;
    }
}
