<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task\Internal;

use Amp\Cluster\Cluster;
use Nayleen\Async\Worker;

$worker = Cluster::getChannel()->receive();
assert($worker instanceof Worker);

$worker->run();
