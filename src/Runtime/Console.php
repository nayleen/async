<?php

declare(strict_types = 1);

namespace Nayleen\Async\Runtime;

use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function Amp\async;

/**
 * @api
 */
final class Console extends Runtime
{
    public function __construct(
        Kernel $kernel,
        private readonly Application $console,
    ) {
        parent::__construct($kernel);
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

    public function run(
        ?OutputInterface $output = null,
        InputInterface $input = new ArgvInput(),
    ): int {
        $output ??= $this->kernel->make(OutputInterface::class);

        $this->console->setAutoExit(false);

        return $this->kernel->run(fn () => async($this->console->run(...), $input, $output));
    }
}
