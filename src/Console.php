<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\ForbidCloning;
use Amp\ForbidSerialization;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

readonly class Console extends Runtime
{
    use ForbidCloning;
    use ForbidSerialization;

    /**
     * @param non-empty-string|null $defaultCommand
     */
    public function __construct(
        private ?string $defaultCommand = null,
        private ?InputInterface $input = null,
        private ?OutputInterface $output = null,
        ?Kernel $kernel = null,
    ) {
        assert($this->defaultCommand !== '');
        parent::__construct($this->runConsole(...), $kernel);
    }

    protected function runConsole(Kernel $kernel): int
    {
        $console = $kernel->container()->get(Application::class);
        $console->setAutoExit(false);

        if (isset($this->defaultCommand)) {
            assert($this->defaultCommand !== '');
            $console->setDefaultCommand($this->defaultCommand);
        }

        return $console->run(
            $this->input ?? $kernel->container()->get(InputInterface::class),
            $this->output ?? $kernel->container()->get(OutputInterface::class),
        );
    }
}
