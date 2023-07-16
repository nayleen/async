<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Symfony\Component\Console\Application as ConsoleApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
class Console extends Application
{
    /**
     * @param non-empty-string|null $defaultCommand
     */
    public function __construct(
        private readonly ?string $defaultCommand = null,
        private readonly ?InputInterface $input = null,
        private readonly ?OutputInterface $output = null,
    ) {
        parent::__construct();

        assert($this->defaultCommand !== '');
    }

    protected function execute(Kernel $kernel): int
    {
        $console = $kernel->container()->get(ConsoleApplication::class);

        if (isset($this->defaultCommand)) {
            assert($this->defaultCommand !== '' && $console->has($this->defaultCommand));
            $console->setDefaultCommand($this->defaultCommand, true);
        }

        return $console->run(
            $this->input ?? $kernel->container()->get(InputInterface::class),
            $this->output ?? $kernel->container()->get(OutputInterface::class),
        );
    }
}
