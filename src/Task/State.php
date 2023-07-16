<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Parallel\Worker\Execution;

/**
 * @internal
 */
enum State
{
    public static function determine(?Execution $execution): self
    {
        return match (true) {
            !isset($execution) => self::STARTING,
            $execution->getChannel()->isClosed() => self::FINISHED,
            default => self::RUNNING,
        };
    }

    case FINISHED;
    case RUNNING;
    case STARTING;
}
