<?php

declare(strict_types = 1);

namespace Nayleen\Async\Task;

use Amp\Parallel\Worker\Execution;

/**
 * @internal
 */
enum Status
{
    case FAILED;
    case FINISHED;
    case STARTING;
    case RUNNING;

    public static function determine(?Execution $execution = null): self
    {
        return match (true) {
            !isset($execution) => self::STARTING,
            $execution->getFuture()->isComplete() => self::FINISHED,
            $execution->getChannel()->isClosed() => self::FAILED,
            default => self::RUNNING,
        };
    }
}
