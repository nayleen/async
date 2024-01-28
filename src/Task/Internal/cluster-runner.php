<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task\Internal;

use Amp\Cluster\Cluster;
use Nayleen\Async\Task;

$task = Cluster::getChannel()->receive();
assert($task instanceof Task);

$task->run();
