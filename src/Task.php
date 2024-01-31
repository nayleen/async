<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Task as AmpTask;
use Closure;
use Nayleen\Async\Task\Scheduler;

/**
 * A simple executable bit of code intended to run in a separate process/thread,
 * by passing it to {@see Scheduler::run()}, returning a value on completion.
 *
 * @template TReceive
 * @template TSend
 * @template TResult of mixed
 * @extends Runtime<TReceive, TSend, TResult>
 */
readonly class Task extends Runtime implements AmpTask
{
    /**
     * @return Task<mixed, mixed, mixed>
     */
    final public static function create(AmpTask|Closure|string $task): self
    {
        assert($task !== '');

        $adapted = match (true) {
            $task instanceof Closure => new self($task),
            is_string($task) => self::fromScript($task),
            !($task instanceof Runtime) => self::fromTask($task),
            default => $task,
        };
        assert($adapted instanceof self);

        return $adapted;
    }

    /**
     * @param non-empty-string $script path to a script returning a closure compatible with {@see Task::__construct}
     * @return Task<mixed, mixed, mixed>
     */
    final public static function fromScript(string $script): self
    {
        assert((static function () use ($script): bool {
            if (!file_exists($script)) {
                return false;
            }

            $return = require $script;
            if (!($return instanceof Closure)) {
                return false;
            }

            return true;
        })());

        return new self((require $script)(...));
    }

    /**
     * @return Task<mixed, mixed, mixed>
     */
    final public static function fromTask(AmpTask $task): self
    {
        return new self(static fn (Kernel $kernel): mixed => $task->run($kernel->channel(), $kernel->cancellation));
    }
}
