<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Nayleen\Async\Kernel;
use Nayleen\Async\Runtime as BaseRuntime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
final class Runtime extends BaseRuntime
{
    /**
     * @paran non-empty-string|null $defaultCommand
     */
    public function __construct(
        private readonly ?string $defaultCommand = null,
        private readonly ?InputInterface $input = null,
        private readonly ?OutputInterface $output = null,
    ) {
        assert($this->defaultCommand !== '');
    }

    protected function execute(Kernel $kernel): int
    {
        $console = $kernel->make(Application::class);

        if (isset($this->defaultCommand)) {
            assert($this->defaultCommand !== '' && $console->has($this->defaultCommand));
            $console->setDefaultCommand($this->defaultCommand, true);
        }

        return $console->run(
            $this->input ?? $kernel->make(InputInterface::class),
            $this->output ?? $kernel->make(OutputInterface::class),
        );
    }
}
