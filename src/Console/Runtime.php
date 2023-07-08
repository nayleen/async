<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console;

use Amp\Cancellation;
use Nayleen\Async\Runtime as BaseRuntime;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
class Runtime extends BaseRuntime
{
    /**
     * @paran non-empty-string|null $defaultCommand
     */
    public function __construct(
        private readonly ?string $defaultCommand = null,
        private readonly ?InputInterface $input = null,
        private readonly ?OutputInterface $output = null,
    ) {
        parent::__construct();

        assert($this->defaultCommand !== '');
    }

    protected function execute(Cancellation $cancellation): int
    {
        $console = $this->kernel->container()->get(Application::class);

        if (isset($this->defaultCommand)) {
            assert($this->defaultCommand !== '' && $console->has($this->defaultCommand));
            $console->setDefaultCommand($this->defaultCommand, true);
        }

        return $console->run(
            $this->input ?? $this->kernel->container()->get(InputInterface::class),
            $this->output ?? $this->kernel->container()->get(OutputInterface::class),
        );
    }
}
