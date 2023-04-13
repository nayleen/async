<?php

declare(strict_types = 1);

namespace Nayleen\Async\Console\Command\Task;

use Nayleen\Async\Console\Command\Task;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\ExceptionInterface as ConsoleException;

/**
 * @internal
 */
final class Validator
{
    public function __construct(private readonly Application $application)
    {

    }

    /**
     * @throws ConsoleException
     */
    public function validate(Task $task): bool
    {
        $task->input->bind($this->application->get($task->command)->getDefinition());

        return true;
    }
}
