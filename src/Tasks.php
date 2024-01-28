<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Task;
use Nayleen\Async\Task\Scheduler;

readonly class Tasks
{
    /**
     * @var Task[]
     */
    private array $tasks;

    public function __construct(Task ...$tasks)
    {
        $map = [];
        foreach ($tasks as $task) {
            $hash = spl_object_hash($task);
            assert($hash !== '');

            $map[$hash] = $task;
        }

        $this->tasks = array_values($map);
    }

    public function submit(Scheduler $scheduler): void
    {
        if (count($this->tasks) === 0) {
            return;
        }

        foreach ($this->tasks as $task) {
            $scheduler->submit($task);
        }
    }
}
