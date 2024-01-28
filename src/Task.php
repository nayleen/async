<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Task as TaskInterface;

abstract class Task extends Runtime implements TaskInterface {}
