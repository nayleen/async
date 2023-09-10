<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Task;
use Nayleen\Async\Task\Scheduler;

class Tasks
{
    /**
     * @var array<string, Task>
     */
    private array $tasks = [];

    public function __construct(Task ...$tasks)
    {
        $this->add(...$tasks);
    }

    public function add(Task ...$tasks): void
    {
        foreach ($tasks as $task) {
            $this->tasks[spl_object_hash($task)] = $task;
        }
    }

    public function schedule(Scheduler $scheduler): void
    {
        if (count($this->tasks) === 0) {
            return;
        }

        foreach ($this->tasks as $task) {
            $scheduler->submit($task);
        }
    }
}
