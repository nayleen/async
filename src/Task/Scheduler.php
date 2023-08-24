<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Future;
use Amp\Parallel\Worker\Task;

interface Scheduler
{
    public function execute(Task $task, ?float $awaitTimeout = null, ?float $submitTimeout = null): mixed;

    public function kill(): void;

    public function shutdown(): void;

    public function submit(Task $task, ?float $timeout = null, bool $monitor = false): Future;
}
