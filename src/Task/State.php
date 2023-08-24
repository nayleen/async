<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

/**
 * @internal
 */
enum State
{
    case FINISHED;
    case RUNNING;
    case STARTING;
}
