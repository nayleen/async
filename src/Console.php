<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
final class Console extends Runtime
{
    public function __construct(
        Kernel $kernel,
        private readonly Application $console,
        private OutputInterface $output,
        private ?InputInterface $input = null,
    ) {
        parent::__construct($kernel);

        $this->console->setAutoExit(false);
    }

    protected function execute(): void
    {
        $this->console->run($this->input, $this->output);
    }

    public function command(string|Command $command): self
    {
        if ($command instanceof Command) {
            $name = (string) $command->getName();

            if (!$this->console->has($name)) {
                $this->console->add($command);
            }

            $command = $name;
        }

        assert($command !== '' && $this->console->has($command));
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
