<?php

declare(strict_types = 1);

namespace Nayleen\Async;

use Amp\Parallel\Worker\Task as TaskInterface;
use Closure;
use Nayleen\Async\Task\Scheduler;

/**
 * A simple executable bit of code intended to run in a separate process/thread,
 * by passing it to {@see Scheduler::run()}, returning a value on completion.
 *
 * @template TReceive
 * @template TSend
 * @template TResult of mixed
 * @template-extends Runtime<TReceive, TSend, TResult>
 */
readonly class Task extends Runtime implements TaskInterface
{
    final public static function create(Closure|string|TaskInterface $task): static
    {
        assert($task !== '');

        $task = match (true) {
            $task instanceof Closure => new self($task),
            is_string($task) => self::fromScript($task),
            !($task instanceof static) => self::fromTask($task),
            default => $task,
        };
        assert($task instanceof static);

        return $task;
    }

    /**
     * @param non-empty-string $filePath path to a script returning a closure compatible with {@see Task::__construct}
     * @return Task<mixed, mixed, mixed>
     */
    final public static function fromScript(string $filePath): self
    {
        assert((static function () use ($filePath): bool {
            if (!file_exists($filePath)) {
                return false;
            }

            $return = require $filePath;
            if (!($return instanceof Closure)) {
                return false;
            }

            return true;
        })());

        return new self((require $filePath)(...));
    }

    /**
     * @return Task<mixed, mixed, mixed>
     */
    final public static function fromTask(TaskInterface $task): self
    {
        return new self(static fn (Kernel $kernel): mixed => $task->run($kernel->channel(), $kernel->cancellation));
    }
}
