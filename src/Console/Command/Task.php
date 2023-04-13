<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command;

use Amp\Cancellation;
use Amp\Parallel\Worker\Task as TaskInterface;
use Amp\Sync\Channel;
use Nayleen\Async\Console\Application;
use Nayleen\Async\Console\Command\Task\Builder;
use Nayleen\Async\Console\Command\Task\Validator;
use Nayleen\Async\Kernel;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @api
 */
final class Task implements TaskInterface
{
    public readonly ArrayInput $input;

    public function __construct(public readonly string $command, ArrayInput $input = null)
    {
        $this->input = $input ?? new ArrayInput(['command' => $this->command]);
    }

    /**
     * @param non-empty-string $command
     */
    public static function build(string $command): Builder
    {
        return new Builder($command);
    }

    public function run(Channel $channel, Cancellation $cancellation): int
    {
        $kernel = new Kernel(channel: $channel, cancellation: $cancellation);

        $console = $kernel->make(Application::class);
        assert((new Validator($console))->validate($this));

        $console->setAutoExit(false);

        return $console->run($this->input, $kernel->make(OutputInterface::class));
    }
}
