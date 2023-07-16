<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Future;
use Amp\Parallel\Worker\Task as TaskInterface;
use Closure;
use Nayleen\Async\Task\AnonymousTask;

abstract class Task extends Runtime implements TaskInterface
{
    /**
     * @template TResult of mixed
     * @param Closure(Kernel): (Future<TResult>|TResult) $closure
     * @return AnonymousTask<TResult>
     */
    public static function create(Closure $closure): AnonymousTask
    {
        return new AnonymousTask($closure);
    }
}
